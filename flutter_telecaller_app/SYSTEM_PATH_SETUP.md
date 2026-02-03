# System Variables mein Flutter PATH Add Karein

## Important: Admin Rights Required

System variables mein add karne ke liye **Administrator rights** chahiye.

---

## Step-by-Step Guide

### Step 1: Environment Variables Dialog Open Karein

1. **Windows + R** press karein
2. Type: `sysdm.cpl` → **Enter**
3. **"Advanced"** tab par click karein
4. **"Environment Variables..."** button par click karein

### Step 2: System Variables Section mein Path Edit Karein

1. **"System variables"** section mein scroll karein
2. **"Path"** variable ko select karein (highlight karein)
3. **"Edit..."** button par click karein

### Step 3: Flutter Path Add Karein

1. **"New"** button par click karein
2. Type: `C:\flutter\bin`
3. **"OK"** button par click karein

### Step 4: Save Karein

1. **"OK"** button par click karein (Environment Variables dialog mein)
2. **"OK"** button par click karein (System Properties dialog mein)

### Step 5: Restart

1. **Terminal/IDE ko close karein**
2. **Naya terminal/IDE open karein** (as Administrator agar needed ho)

---

## Verification

Naye terminal mein:

```bash
flutter --version
```

Agar Flutter version dikhe, toh setup successful hai! ✓

---

## Note

- **System variables:** Sab users ke liye (admin rights required)
- **User variables:** Sirf current user ke liye (no admin needed)

Dono methods work karte hain, lekin System variables mein add karne se sab users ko access milega.
