"""
Simple demand forecast per product: average units sold per weekday over the last N weeks.
No ML needed for a bare-minimum version; this already answers "how much ice should I prepare tomorrow?".

Usage:
    python ai/services/forecasting.py                # forecast next 7 days
    python ai/services/forecasting.py --days 3 --weeks 8 --json
"""
import argparse
import json
from collections import defaultdict
from datetime import date, timedelta

import mysql.connector

DB = dict(host="localhost", user="root", password="", database="ice_ordering_system")


def load_sales(weeks: int):
    conn = mysql.connector.connect(**DB)
    try:
        cur = conn.cursor(dictionary=True)
        cur.execute(
            """
            SELECT oi.product_name, DATE(o.created_at) AS day, SUM(oi.quantity) AS qty
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status <> 'Cancelled' AND o.created_at >= %s
            GROUP BY oi.product_name, DATE(o.created_at)
            """,
            (date.today() - timedelta(weeks=weeks),),
        )
        return cur.fetchall()
    finally:
        conn.close()


def forecast(days: int, weeks: int) -> dict:
    # totals[product][weekday] = total units sold on that weekday in the window
    totals = defaultdict(lambda: defaultdict(int))
    for row in load_sales(weeks):
        totals[row["product_name"]][row["day"].weekday()] += int(row["qty"])

    result = {}
    for offset in range(1, days + 1):
        target = date.today() + timedelta(days=offset)
        result[target.isoformat()] = {
            product: round(by_weekday[target.weekday()] / weeks, 1)
            for product, by_weekday in totals.items()
        }
    return result


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Forecast ice demand")
    parser.add_argument("--days", type=int, default=7)
    parser.add_argument("--weeks", type=int, default=4, help="history window in weeks")
    parser.add_argument("--json", action="store_true", help="print JSON (for PHP/other tools)")
    args = parser.parse_args()

    data = forecast(args.days, args.weeks)
    if args.json:
        print(json.dumps(data))
    else:
        for day, products in data.items():
            print(day, date.fromisoformat(day).strftime("%A"))
            if not products:
                print("  (not enough data yet)")
            for name, qty in products.items():
                print(f"  {name}: ~{qty} units")

