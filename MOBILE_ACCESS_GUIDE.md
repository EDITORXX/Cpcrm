# Mobile/Tablet से Website Access करने का Guide

## 🚀 Quick Start

### Step 1: Server Start करें
```bash
start_server.bat
```

Server start होने के बाद आपको दो URLs दिखेंगे:
- **Local**: `http://localhost:8007` (केवल इसी computer पर)
- **Network**: `http://YOUR_IP:8007` (same network के सभी devices पर)

### Step 2: अपना IP Address जानें

**Windows में:**
```bash
ipconfig
```
"IPv4 Address" के आगे जो IP address है, वो use करें (जैसे: `192.168.1.7`)

### Step 3: Mobile/Tablet पर Access करें

1. **Same Wi-Fi Network पर connect करें** (जिस network पर आपका computer है)
2. Mobile browser में ये URL खोलें:
   ```
   http://YOUR_IP_ADDRESS:8007
   ```
   उदाहरण: `http://192.168.1.7:8007`

## ⚠️ Important Notes

### 1. Same Network होना जरूरी है
- Mobile और Computer दोनों same Wi-Fi network पर होने चाहिए
- Mobile data use न करें

### 2. Firewall Settings
Windows Firewall port 8007 को block कर सकता है। अगर mobile से connect नहीं हो रहा:

**Option 1: Firewall Rule Add करें**
```powershell
# Run PowerShell as Administrator
New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8007 -Protocol TCP -Action Allow
```

**Option 2: Temporarily Firewall Disable करें (Testing के लिए)**
- Windows Security → Firewall & network protection
- Private network के लिए Firewall temporarily off करें

### 3. IP Address Change हो सकता है
- Router restart होने पर IP address change हो सकता है
- हर बार server start करने पर IP check करें

## 🔧 Troubleshooting

### Problem: Mobile से website नहीं खुल रही

**Solution 1: IP Address Verify करें**
```bash
ipconfig
```
सही IP address use कर रहे हैं या नहीं check करें

**Solution 2: Server Running है या नहीं**
- Computer पर `http://localhost:8007` खोलकर check करें
- अगर localhost पर नहीं खुल रहा, तो server start नहीं हुआ है

**Solution 3: Firewall Check करें**
- Windows Firewall में port 8007 allow है या नहीं check करें
- Antivirus software भी block कर सकता है

**Solution 4: Network Check करें**
- Mobile और Computer same Wi-Fi पर हैं या नहीं verify करें
- Mobile में Wi-Fi settings में connected network name check करें

### Problem: "Connection Refused" Error

**Solution:**
- Server `--host=0.0.0.0` के साथ start हुआ है या नहीं check करें
- `start_server.bat` file में `--host=0.0.0.0` parameter होना चाहिए

### Problem: IP Address हर बार Change होता है

**Solution:**
Router में static IP address set करें या DHCP reservation configure करें।

## 📱 Mobile Browser में Use करें

1. **Chrome/Edge**: Direct URL type करें
2. **Safari (iOS)**: URL bar में IP address type करें
3. **Firefox**: Same as Chrome

## 🔒 Security Note

यह configuration केवल **local development** के लिए है। Production में:
- Proper authentication use करें
- HTTPS enable करें
- Firewall rules properly configure करें

## 📝 Example

अगर आपका IP address `192.168.1.7` है:

1. Computer पर:
   ```bash
   start_server.bat
   ```
   Output:
   ```
   Server is now accessible at:
   Local:  http://localhost:8007
   Network: http://192.168.1.7:8007
   ```

2. Mobile browser में:
   ```
   http://192.168.1.7:8007
   ```

3. Website mobile पर खुल जाएगी! 🎉

