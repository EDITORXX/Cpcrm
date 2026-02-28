# Facebook Lead Ads – Testing Guide

Yeh guide se aap standalone Facebook Lead Ads integration ko bina production Meta app ke bhi test kar sakte ho (Settings + UI), aur jab Meta app + webhook ready ho to full flow test kar sakte ho.

---

## Part 1: CRM UI Test (bina Meta ke)

### 1.1 Integrations par jao

1. Login karo (Admin ya CRM role).
2. **Integrations** menu open karo.
3. **"Facebook Lead Ads"** card dikhega (alag, purana "Meta Sheet" / "Facebook Meta" se).
4. **Configure** click karo → Facebook Lead Ads landing page khulegi.

### 1.2 Settings page

1. **Settings** click karo.
2. **Page Access Token** – abhi test ke liye kuch bhi paste kar sakte ho (invalid token se "Test connection" fail karega, jo expected hai).
3. **Graph API version** – `v18.0` ya `v21.0` rakho.
4. **Test connection** click karo:
   - Agar token **galat** hai → error message aana chahiye (e.g. "Invalid OAuth access token").
   - Agar token **sahi** hai (niche Part 2 se long‑lived token banao) → "Connection successful" + pages list aani chahiye.
5. **Save settings** click karo → koi error nahi aana chahiye, redirect ho jayega.

### 1.3 Form selector (jab token + page set ho)

1. Settings mein **sahi token** + **Page ID** save karo (Test connection ke baad "Use this page" se).
2. **Select Form** / **Facebook Lead Ads** → **Select Form** click karo.
3. Agar token/page sahi hai → apne page ke **leadgen forms** list honge.
4. Koi form pe **Configure** click karo → Mapping page khulegi.

### 1.4 Mapping page

1. Left column: **Facebook fields** (full_name, email, phone_number, etc.).
2. Right column: **CRM field** dropdown (name, email, phone, city, state, pincode, address, requirements, notes, meta).
3. **Save & Enable** click karo → success message + redirect to index.
4. Index par woh form "Configured forms" list mein dikhna chahiye, status **Enabled**.

---

## Part 2: Meta App + Token (real test ke liye)

### 2.1 Meta for Developers par app banao

1. https://developers.facebook.com/ par jao.
2. **My Apps** → **Create App** → **Business** (ya **Other**) choose karo.
3. App name do, Create karo.

### 2.2 Facebook Login + Webhooks add karo

1. App dashboard → **Add Products**.
2. **Facebook Login** (optional, agar token manually banao to).
3. **Webhooks** add karo → **Page** object choose karo.
4. **Page** → **Subscribe** → yeh details bharo:
   - **Callback URL:**  
     `https://YOUR-DOMAIN.com/api/webhooks/facebook/leads`  
     (local test ke liye: **ngrok** ya **cloudflare tunnel** use karke public URL banao, e.g. `https://abc123.ngrok.io/api/webhooks/facebook/leads`)
   - **Verify token:** koi bhi string (e.g. `my_verify_token_123`) – yahi value CRM **Settings** → **Webhook verify token** mein daalna hai.

5. Subscribe ke baad **leadgen** field subscribe karo (Lead Ads ke liye):
   - Webhooks → Page → **Edit subscription** → **leadgen** checkbox on → Save.

### 2.3 Page Access Token (long‑lived)

1. **Graph API Explorer:** https://developers.facebook.com/tools/explorer/
2. App select karo, **User or Page** token choose karo.
3. **Page** select karo (jo page pe Lead Ad form hai).
4. **Permissions** mein add karo:
   - `leads_retrieval`
   - `pages_read_engagement`
   - `pages_show_list`
   - `pages_manage_metadata` (agar chahiye ho).
5. **Generate Access Token** → authorize karo.
6. Ye **short‑lived** token hota hai. **Long‑lived** banane ke liye:
   - Tool use karo: https://developers.facebook.com/tools/accesstoken/
   - Ya API call:  
     `GET https://graph.facebook.com/v18.0/oauth/access_token?grant_type=fb_exchange_token&client_id=APP_ID&client_secret=APP_SECRET&fb_exchange_token=SHORT_LIVED_TOKEN`
7. Jo **long‑lived Page Access Token** mile, use **copy** karo.

### 2.4 CRM mein token + page set karo

1. CRM → **Integrations** → **Facebook Lead Ads** → **Settings**.
2. **Page Access Token** mein long‑lived token paste karo.
3. **Test connection** click karo → pages list aani chahiye.
4. Apne page pe **Use this page** click karo (Page ID + Page name auto fill ho jayega).
5. **Webhook verify token** mein wahi string daalo jo Meta Webhooks subscription mein diya tha (e.g. `my_verify_token_123`).
6. **Save settings** karo.

---

## Part 3: Webhook verification test (GET)

Meta jab **Webhooks** subscribe karta hai to ek **GET** request bhejta hai. CRM ko `hub.challenge` return karna hota hai.

### 3.1 Browser ya curl se

Browser mein open karo (replace YOUR_DOMAIN aur verify token):

```
https://YOUR_DOMAIN.com/api/webhooks/facebook/leads?hub.mode=subscribe&hub.verify_token=my_verify_token_123&hub.challenge=test123
```

- **Expected:** sirf `test123` (ya jo bhi `hub.challenge` value di) page par dikhe.  
- Agar **403** aaye to check karo: CRM Settings mein **Webhook verify token** exactly wahi hai jo URL mein `hub.verify_token` hai.

---

## Part 4: Lead sync test (POST webhook + queue)

Jab koi user **Facebook Lead Ad** submit karta hai, Meta **POST** request bhejta hai. CRM usko receive karke job queue karta hai; job lead details Graph API se fetch karke `fb_leads` mein save karta hai.

### 4.1 Queue worker chalao

Job database queue use karta hai. Worker run karo:

```bash
php artisan queue:work
```

(Agar Redis use karte ho to `QUEUE_CONNECTION=redis` set karke same command.)

### 4.2 Real lead se test

1. Facebook par apna **Lead Ad** run karo (ya test lead submit karo).
2. Jab form submit ho:
   - Meta **POST** `/api/webhooks/facebook/leads` par bhejega.
   - CRM **fb_webhook_events** mein row create karega (status `received`).
   - **FetchFacebookLeadDetailsJob** queue mein jayega.
3. **Queue worker** job run karega:
   - Graph API se lead details fetch.
   - **fb_leads** mein nayi row (leadgen_id unique).
4. Check karo:
   - **fb_webhook_events:** nayi entry, status `processed` (ya error hone par `failed`).
   - **fb_leads:** nayi row, `field_data_json` mein form ke answers.

### 4.3 Manual POST test (optional)

Agar abhi real lead nahi hai to **curl** se dummy POST bhej sakte ho (form_id CRM mein configured hona chahiye):

```bash
curl -X POST "https://YOUR_DOMAIN.com/api/webhooks/facebook/leads" \
  -H "Content-Type: application/json" \
  -d '{
    "object": "page",
    "entry": [{
      "id": "PAGE_ID",
      "time": 1234567890,
      "changes": [{
        "field": "leadgen",
        "value": {
          "leadgen_id": "LEADGEN_ID_FROM_META",
          "form_id": "YOUR_FORM_ID",
          "ad_id": "123"
        }
      }]
    }]
  }'
```

- **LEADGEN_ID_FROM_META** – koi bhi valid leadgen_id (Graph API se ya real lead submit karke logs se).
- **YOUR_FORM_ID** – woh form_id jo CRM **Forms** list mein hai aur jiska mapping enable hai.

Agar form_id galat hai to **fb_webhook_events** mein status `failed`, error "Form not found". Agar sahi hai to job run hogi (lekin Graph API se fetch tabhi success hoga jab leadgen_id real ho).

---

## Part 5: Local testing (ngrok / tunnel)

Meta ko **public HTTPS URL** chahiye. Local server (e.g. `http://localhost:8000`) direct use nahi kar sakte.

### 5.1 ngrok

1. https://ngrok.com/ se ngrok install karo.
2. Laravel chalao: `php artisan serve` (ya apna server).
3. Dusri terminal: `ngrok http 8000` (ya jo port ho).
4. ngrok jo **https** URL de (e.g. `https://abc123.ngrok.io`) use karo:
   - Meta Webhooks **Callback URL:** `https://abc123.ngrok.io/api/webhooks/facebook/leads`
   - **Verify token** CRM Settings mein same daalo.

### 5.2 Cloudflare Tunnel

Agar Cloudflare Tunnel use karte ho to apna public URL wahan set karo; baaki steps same – callback URL hamesha `https://YOUR_PUBLIC_URL/api/webhooks/facebook/leads`.

---

## Checklist

- [ ] Integrations par "Facebook Lead Ads" card dikh raha hai
- [ ] Settings open ho rahi hai, Save ho raha hai
- [ ] Test connection – galat token → error; sahi token → pages list
- [ ] Page select karke Save kiya
- [ ] Forms list aa rahi hai (sahi token + page ke saath)
- [ ] Ek form pe Configure → Mapping page, Save & Enable
- [ ] Index par form "Enabled" dikh raha hai
- [ ] Webhook verify token CRM + Meta dono jagah same
- [ ] GET webhook URL browser/curl se `hub.challenge` return kar raha hai
- [ ] Queue worker chal raha hai (`php artisan queue:work`)
- [ ] Real lead submit → fb_webhook_events + fb_leads mein entry
- [ ] (Optional) Signature verification off rakho jab tak test kar rahe ho

---

## Troubleshooting

| Problem | Check |
|--------|--------|
| Test connection fails | Token sahi hai? Long‑lived Page token? Permissions `leads_retrieval`, `pages_read_engagement`? |
| Forms list empty | Page ID sahi save hua? Usi page pe Lead Ad forms hain? |
| Webhook GET 403 | CRM **Webhook verify token** = Meta subscription wala verify token |
| Webhook POST 401 | Agar signature verification on hai to **App secret** sahi hai? |
| Lead create nahi ho raha | Queue worker chal raha hai? **fb_webhook_events** mein entry aa rahi hai? Form_id CRM form se match kar raha hai? |
| Duplicate lead | Job **leadgen_id** se dedupe karti hai – same leadgen_id dobara insert nahi hota. |

---

Jab sab test pass ho jaye, baad mein CRM leads table integration (FB_LEADS_CREATE_CRM_LEAD) enable karke production par same flow use kar sakte ho.
