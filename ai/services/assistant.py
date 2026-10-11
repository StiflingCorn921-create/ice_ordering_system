"""
Terminal chat with the local Ollama model, using the same shop context as the website.
Handy for testing prompts/models before changing app/services/AIService.php.

Usage:
    pip install -r ai/data/requirements.txt
    ollama pull llama3
    python ai/services/assistant.py              # chat as a customer
    python ai/services/assistant.py --model qwen2.5:3b
"""
import argparse

import mysql.connector
import requests

OLLAMA_URL = "http://localhost:11434/api/chat"
DB = dict(host="localhost", user="root", password="", database="ice_ordering_system")

SYSTEM_PROMPT = """You are the friendly assistant of Yulio's Purified Cube Ice, a small ice business in the Philippines.
Help customers choose how much ice to order. Rule of thumb: about 1 kg of ice per 2-3 people for a party,
more on hot days or for coolers. Only recommend products from the list below. Keep answers short (max 4 sentences).
Products:
{products}"""


def load_products() -> str:
    conn = mysql.connector.connect(**DB)
    try:
        cur = conn.cursor(dictionary=True)
        cur.execute("SELECT name, weight_kg, retail_price FROM products WHERE is_active = 1 ORDER BY weight_kg")
        return "\n".join(f"- {p['name']} ({p['weight_kg']} kg): PHP {p['retail_price']}" for p in cur.fetchall())
    finally:
        conn.close()


def chat(model: str) -> None:
    messages = [{"role": "system", "content": SYSTEM_PROMPT.format(products=load_products())}]
    print(f"Chatting with {model}. Type 'exit' to quit.\n")

    while True:
        user = input("You: ").strip()
        if user.lower() in {"exit", "quit"}:
            break
        if not user:
            continue

        messages.append({"role": "user", "content": user})
        try:
            res = requests.post(OLLAMA_URL, json={"model": model, "messages": messages, "stream": False}, timeout=120)
            res.raise_for_status()
        except requests.RequestException as exc:
            print(f"[error] Could not reach Ollama: {exc}")
            messages.pop()
            continue

        reply = res.json()["message"]["content"].strip()
        messages.append({"role": "assistant", "content": reply})
        print(f"Bot: {reply}\n")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Chat with the ice shop assistant")
    parser.add_argument("--model", default="llama3")
    chat(parser.parse_args().model)

