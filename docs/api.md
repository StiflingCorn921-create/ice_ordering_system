# API Documentation

Base: `/ice_ordering_system/api/`. All responses are formatted in UTF-8 JSON.

## Authentication Models

The API supports dual authentication:
1. **Asymmetric Bearer Tokens (RS256 JWT):** 
   - Signed by the server using an **RSA 2048-bit Private Key** (`storage/keys/private.pem`).
   - Verified using the corresponding **RSA Public Key** (`storage/keys/public.pem`).
   - Pass in HTTP headers: `Authorization: Bearer <token>`.
   - Immune to CSRF; no CSRF token required when using Bearer auth.
2. **Session Cookies (Browser Web UI):**
   - Traditional session auth with CSRF header (`X-CSRF-Token`).

---

## Endpoints

### `auth.php`

| Method | Query / Body | Access | Result |
| :--- | :--- | :--- | :--- |
| **GET** | — | Protected | Current authenticated user object and token |
| **GET** | `?action=public_key` | Public | Returns the RSA 2048-bit Public Key (PEM format) |
| **POST** | `{action: "login", username, password}` | Public | `{user, token, token_type: "Bearer", csrf_token}` |
| **POST** | `{action: "logout"}` | Protected | `{ok: true}` |

### `products.php`

| Method | Query | Access | Result |
| :--- | :--- | :--- | :--- |
| **GET** | — | Public | Active products catalog (retail prices only, exact stock hidden) |
| **GET** | `?all=1` | Admin | Complete product database including stock quantities & reseller tiers |

### `orders.php`

| Method | Query / Body | Access | Result |
| :--- | :--- | :--- | :--- |
| **GET** | `?order_number=ORD-...&phone=09...` | Public | Order tracking details with ordered items |
| **GET** | `?status=Pending&q=search` | Admin | Full order list with items, addresses, payment status |
| **POST** | `{action: "create", customer_name, contact_number, delivery_address, fulfillment_type, payment_method, delivery_date?, notes?, items: {product_id: qty}}` | Public | `201` Created order receipt, or `422` validation errors |
| **POST** | `{action: "status", order_id, status}` | Admin | `200` status updated (cancelling returns stock) |

### `customers.php`

| Method | Query | Access | Result |
| :--- | :--- | :--- | :--- |
| **GET** | `?q=search` | Admin | Customer directory with order counts and lifetime expenditure |

### `ai.php`

| Method | Body | Access | Result |
| :--- | :--- | :--- | :--- |
| **POST** | `{message, mode: "customer"}` | Public | Ollama ice calculation and recommendation |
| **POST** | `{message, mode: "admin"}` | Admin | Business intelligence answering from live inventory & sales logs |

*Rate Limit:* 10 queries per minute per session to conserve compute.
