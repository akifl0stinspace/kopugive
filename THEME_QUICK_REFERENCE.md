# Maroon & Gold Theme - Quick Reference

## 🎨 Color Codes

### Maroon
- **Primary:** `#800020`
- **Dark:** `#5c0016`
- **Light:** `#a6002b`

### Gold
- **Primary:** `#FFD700`
- **Dark:** `#DAA520`
- **Light:** `#FFED4E`

### Gradients
- **Maroon-Gold:** `linear-gradient(135deg, #800020 0%, #DAA520 100%)`
- **Maroon Only:** `linear-gradient(180deg, #800020 0%, #5c0016 100%)`

---

## 📁 Theme File Location

**Main Theme File:** `includes/theme_styles.php`

This file is included in all pages and contains:
- CSS variables
- All component styles
- Responsive design rules
- Print styles

---

## 🔧 How to Update Colors

1. Open `includes/theme_styles.php`
2. Find the `:root` section at the top
3. Change the color values:

```css
:root {
    --maroon-primary: #800020;  /* Change this */
    --gold-primary: #FFD700;    /* Change this */
}
```

4. Save - all pages update automatically!

---

## 📋 What Changed

### Before (Purple)
- `#667eea` (Purple)
- `#764ba2` (Dark Purple)

### After (Maroon & Gold)
- `#800020` (Maroon)
- `#DAA520` (Gold)

---

## ✅ Pages Updated

### Admin
- Dashboard
- Campaigns
- Donations
- Donors
- Reports
- Settings

### Donor
- Dashboard
- Browse Campaigns
- Campaign View
- My Donations
- Profile

### Auth
- Login
- Register

### Public
- Home Page (index.php)

---

## 🎯 Key Components

| Component | Color |
|-----------|-------|
| **Navbar** | Maroon-Gold Gradient |
| **Sidebar** | Maroon Gradient |
| **Primary Buttons** | Maroon |
| **Secondary Buttons** | Gold |
| **Card Headers** | Maroon-Gold Gradient |
| **Progress Bars** | Maroon-Gold Gradient |
| **Links** | Maroon |
| **Badges (Primary)** | Maroon |
| **Badges (Secondary)** | Gold |

---

## 💡 Quick Tips

### Using Theme Colors in HTML

**Primary Button:**
```html
<button class="btn btn-primary">Click Me</button>
```

**Secondary Button:**
```html
<button class="btn btn-secondary">Click Me</button>
```

**Primary Badge:**
```html
<span class="badge bg-primary">New</span>
```

**Gold Badge:**
```html
<span class="badge bg-secondary">Featured</span>
```

### Using CSS Variables

```css
.my-element {
    color: var(--maroon-primary);
    background: var(--gold-primary);
    border: 2px solid var(--maroon-dark);
}
```

---

## 🚀 Benefits

✅ **Professional** - Perfect for educational institutions  
✅ **Consistent** - Same colors across all pages  
✅ **Easy to Maintain** - Change colors in one place  
✅ **Accessible** - High contrast for readability  
✅ **Modern** - Clean, elegant design  

---

## 📱 Responsive

The theme works perfectly on:
- 💻 Desktop
- 📱 Mobile
- 📱 Tablet

---

## 🖨️ Print-Friendly

Print styles are included:
- Removes backgrounds
- Shows content clearly
- Maintains branding

---

**Need to change colors?** Edit `includes/theme_styles.php` → `:root` section

