# Reports & Analytics - User Guide

## Quick Start Guide

### Accessing Reports
1. Log in to KopuGive as an administrator
2. Click **Reports & Analytics** in the left sidebar
3. You'll see the reports dashboard with filters at the top

---

## Feature Walkthrough

### 📊 Filter Section (Blue Card at Top)

#### Campaign Filter
```
┌─────────────────────────────────┐
│ Campaign: [All Campaigns ▼]    │
└─────────────────────────────────┘
```
- **Purpose**: Choose which campaign(s) to analyze
- **Options**: 
  - "All Campaigns" - Combined view of everything
  - Individual campaigns - Focus on one campaign
- **When to use**: 
  - Use "All Campaigns" for overall performance
  - Select specific campaign for detailed analysis

#### Time Period Filter
```
┌─────────────────────────────────┐
│ Time Period: [Last 30 Days ▼]  │
└─────────────────────────────────┘
```
- **Purpose**: Set the date range for analysis
- **Options**:
  - **All Time**: Complete history (default)
  - **Today**: Current day only
  - **Last 7 Days**: Weekly report
  - **Last 30 Days**: Monthly report
  - **Last 90 Days**: Quarterly report
  - **Last Year**: Annual report
  - **Custom Range**: Pick specific dates

#### Custom Date Range
When you select "Custom Range", two date pickers appear:
```
┌──────────────┐  ┌──────────────┐
│ Start Date   │  │ End Date     │
│ [2024-01-01] │  │ [2024-12-31] │
└──────────────┘  └──────────────┘
```
- Click the date fields to open calendar picker
- Select your desired date range
- Click search button to apply

---

## Use Cases & Examples

### Example 1: Check Today's Donations
**Goal**: See how much was donated today

**Steps**:
1. Campaign: Select "All Campaigns"
2. Period: Select "Today"
3. Click 🔍 Search button

**Result**: Dashboard shows only today's activity across all campaigns

---

### Example 2: Analyze Specific Campaign Performance
**Goal**: See how "Help Student Education" campaign is doing this month

**Steps**:
1. Campaign: Select "Help Student Education"
2. Period: Select "Last 30 Days"
3. Click 🔍 Search button

**Result**: 
- Campaign details card appears at top
- 8 summary cards show detailed metrics
- Charts show campaign-specific data
- Daily timeline chart appears
- Tables show campaign-specific donors

---

### Example 3: Quarterly Report for All Campaigns
**Goal**: Generate Q4 report for board meeting

**Steps**:
1. Campaign: Select "All Campaigns"
2. Period: Select "Last 90 Days"
3. Click 🔍 Search button
4. Click "Export CSV" button to download data
5. Click "Print Report" button to create PDF

**Result**: Professional report ready for presentation

---

### Example 4: Custom Date Range Analysis
**Goal**: Compare performance between two specific dates

**Steps**:
1. Campaign: Select desired campaign (or All)
2. Period: Select "Custom Range"
3. Start Date: Pick start date (e.g., 2024-01-01)
4. End Date: Pick end date (e.g., 2024-06-30)
5. Click 🔍 Search button

**Result**: Data filtered to exact date range specified

---

## Understanding the Dashboard

### Summary Cards (Top Section)

#### When Viewing All Campaigns (4 cards):
```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│ 💰 Total Raised │  │ ✅ Verified     │  │ ⏳ Pending      │  │ 👥 Total Donors │
│   RM 50,000     │  │    125          │  │    15           │  │    89           │
└─────────────────┘  └─────────────────┘  └─────────────────┘  └─────────────────┘
```

#### When Viewing Individual Campaign (8 cards):
```
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│ 💰 Total Raised │  │ ✅ Verified     │  │ ⏳ Pending      │  │ 👥 Total Donors │
│   RM 15,000     │  │    45           │  │    5            │  │    32           │
└─────────────────┘  └─────────────────┘  └─────────────────┘  └─────────────────┘

┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐
│ 📊 Avg Donation │  │ 👤 Unique Donors│  │ 📈 Progress     │  │ ⏱️ Pending Amt  │
│   RM 333.33     │  │    32           │  │    75%          │  │    RM 500       │
└─────────────────┘  └─────────────────┘  └─────────────────┘  └─────────────────┘
```

### Charts Section

#### Monthly Donation Trends (Line Chart)
- **Purple Line**: Total amount raised per month
- **Pink Line**: Number of donations per month
- **Hover**: See exact values
- **Shows**: Last 12 months

#### Status Distribution (Doughnut Chart)
- **All Campaigns View**: 
  - Green: Active campaigns
  - Gray: Draft campaigns
  - Blue: Completed campaigns
  - Red: Closed campaigns
- **Individual Campaign View**:
  - Green: Verified donations
  - Yellow: Pending donations
  - Red: Rejected donations

#### Daily Timeline (Bar Chart) - Individual Campaign Only
- **Blue Bars**: Daily donation amounts
- **X-axis**: Dates
- **Y-axis**: Amount in RM
- **Shows**: Last 30 days

### Tables Section

#### Top Campaigns Table
```
Campaign Name              | Donations | Amount Raised | Progress
---------------------------|-----------|---------------|----------
Help Student Education     |    45     | RM 15,000    | [████████░░] 75%
Medical Emergency Fund     |    32     | RM 12,000    | [██████░░░░] 60%
School Infrastructure      |    28     | RM 10,000    | [█████░░░░░] 50%
```

#### Top Donors Table
```
#  | Donor Name          | Donations | Total Donated
---|---------------------|-----------|---------------
🥇 | Ahmad bin Ali       |    12     | RM 5,000
🥈 | Siti Nurhaliza      |    8      | RM 3,500
🥉 | John Doe            |    6      | RM 2,800
4  | Jane Smith          |    5      | RM 2,000
```

---

## Export & Print Features

### Export to CSV
**Purpose**: Download data for Excel/spreadsheet analysis

**Steps**:
1. Configure filters as desired
2. Click "Export CSV" button (green)
3. File downloads automatically
4. Open in Excel, Google Sheets, etc.

**File includes**:
- Summary statistics
- Top campaigns data
- Top donors data
- Filename: `kopugive_report_2024-12-02.csv`

### Print Report
**Purpose**: Create PDF or printed report

**Steps**:
1. Configure filters as desired
2. Click "Print Report" button (blue)
3. Print dialog opens
4. Select printer or "Save as PDF"
5. Adjust settings if needed
6. Print/Save

**Print features**:
- Removes navigation sidebar
- Removes buttons
- Optimized layout
- Professional formatting

---

## Tips & Best Practices

### 💡 Tip 1: Regular Monitoring
Check reports daily using "Today" filter to monitor current activity

### 💡 Tip 2: Weekly Reviews
Every Monday, run "Last 7 Days" report to review weekly performance

### 💡 Tip 3: Monthly Reports
End of month: Generate "Last 30 Days" report and export CSV for records

### 💡 Tip 4: Campaign Analysis
When campaign is underperforming, use individual campaign view to analyze:
- Average donation amount
- Donor engagement
- Daily patterns

### 💡 Tip 5: Donor Recognition
Use "Top Donors" table to identify supporters for thank-you messages

### 💡 Tip 6: Clear Filters
If data looks wrong, check active filters. Click red "Clear Filters" badge to reset

---

## Troubleshooting

### ❓ No Data Showing
**Problem**: Dashboard shows zeros or "No data available"

**Solutions**:
1. Check if filters are too restrictive
2. Verify campaign has donations in selected period
3. Click "Clear Filters" badge to reset
4. Try "All Time" period

### ❓ Charts Not Displaying
**Problem**: Chart areas are blank

**Solutions**:
1. Refresh the page (F5)
2. Clear browser cache
3. Check internet connection (Chart.js loads from CDN)
4. Try different browser

### ❓ Export Not Working
**Problem**: CSV doesn't download

**Solutions**:
1. Check browser pop-up blocker
2. Allow downloads from site
3. Try different browser
4. Check browser console for errors (F12)

### ❓ Wrong Date Range
**Problem**: Data doesn't match expected period

**Solutions**:
1. Check active filter badges
2. Verify date format is correct
3. Ensure end date is after start date
4. Clear filters and try again

---

## Keyboard Shortcuts

- **Ctrl+P** (Windows) / **Cmd+P** (Mac): Quick print
- **F5**: Refresh page
- **F12**: Open browser console (for troubleshooting)

---

## Common Workflows

### Daily Morning Routine
```
1. Open Reports page
2. Check "Today" filter
3. Review overnight donations
4. Check pending donations
```

### Weekly Team Meeting
```
1. Set filter to "Last 7 Days"
2. Export CSV
3. Print report
4. Review with team
```

### Monthly Board Report
```
1. Set filter to "Last 30 Days"
2. Review all metrics
3. Export CSV for detailed analysis
4. Print professional report
5. Present to board
```

### Campaign Performance Review
```
1. Select specific campaign
2. Set period to "All Time"
3. Review progress percentage
4. Check daily timeline
5. Identify patterns
6. Make decisions
```

---

## Need Help?

If you encounter issues:
1. Check this guide first
2. Try clearing filters
3. Refresh the page
4. Contact system administrator

---

**Last Updated**: December 2024
**Version**: 2.0
**Compatible With**: KopuGive Admin Panel v1.0+

