# Reports & Analytics Enhancement

## Overview
The reports page has been significantly enhanced with professional filtering, individual campaign reports, and comprehensive analytics capabilities.

## New Features

### 1. **Advanced Filtering System**

#### Campaign Filter
- **All Campaigns**: View aggregated data across all campaigns (default)
- **Individual Campaign**: Select specific campaign for detailed analysis
- Dropdown populated with all available campaigns

#### Time Period Filter
- **All Time**: Complete historical data (default)
- **Today**: Current day's data
- **Last 7 Days**: Weekly report
- **Last 30 Days**: Monthly report
- **Last 90 Days**: Quarterly report
- **Last Year**: Annual report
- **Custom Range**: Select specific start and end dates

### 2. **Enhanced Summary Cards**

#### General Report (All Campaigns)
- Total Raised
- Verified Donations
- Pending Donations
- Total Donors

#### Individual Campaign Report (Additional Metrics)
- Average Donation Amount
- Unique Donors for Campaign
- Campaign Progress Percentage
- Pending Amount

### 3. **Dynamic Charts**

#### Monthly Donation Trends
- Line chart showing donation amounts over time
- Displays both total raised and number of donations
- Adapts to selected filters

#### Status Distribution
- **All Campaigns View**: Campaign status breakdown (Active, Draft, Completed, Closed)
- **Individual Campaign View**: Donation status breakdown (Verified, Pending, Rejected)

#### Daily Timeline (Individual Campaign Only)
- Bar chart showing daily donation activity
- Only appears when viewing specific campaign
- Shows last 30 days of activity

### 4. **Enhanced Data Tables**

#### Top Campaigns Table
- Campaign name with target amount
- Number of donations received
- Total amount raised
- Visual progress bar with percentage
- Sorted by amount raised

#### Top Donors Table
- Ranked list with medals for top 3
- Donor name and email
- Number of donations made
- Total amount donated
- Sorted by total donated

### 5. **Export Functionality**

#### CSV Export
- Generates downloadable CSV file
- Includes:
  - Summary statistics
  - Top campaigns data
  - Top donors data
- Filename includes date: `kopugive_report_YYYY-MM-DD.csv`

#### Print Report
- Print-friendly layout
- Removes navigation and buttons
- Optimized page breaks
- Professional formatting

### 6. **Individual Campaign Details**

When a specific campaign is selected:
- **Campaign Overview Card**
  - Campaign name and description
  - Current status badge
  - Creation date
  - Target amount
  - Campaign image

- **Extended Metrics**
  - Total donations count
  - Unique donors count
  - Average donation amount
  - Pending amount

## Usage Guide

### Viewing General Reports
1. Navigate to **Reports & Analytics** from admin sidebar
2. Default view shows all campaigns combined
3. Review summary cards, charts, and top performers

### Viewing Individual Campaign Reports
1. In the filter section, select a campaign from the dropdown
2. Optionally select a time period
3. Click the search button
4. View detailed campaign metrics and timeline

### Using Time Filters
1. Select desired time period from dropdown
2. For custom range:
   - Select "Custom Range" from period dropdown
   - Choose start date
   - Choose end date
   - Click search button

### Exporting Reports
1. Configure desired filters
2. Click **Export CSV** button for spreadsheet format
3. Click **Print Report** button for PDF (print to PDF)

### Clearing Filters
- Click the red "Clear Filters" badge to reset all filters
- Returns to default view (all campaigns, all time)

## Technical Details

### Database Queries
- All queries are optimized with proper filtering
- Uses prepared statements for security
- Efficient JOIN operations for related data
- Aggregation functions for statistics

### Performance
- Queries limited to relevant data only
- Chart data capped at reasonable limits (12 months, 30 days, etc.)
- Top lists limited to 10 entries

### Security
- All user inputs sanitized
- SQL injection protection via PDO prepared statements
- XSS protection with htmlspecialchars()
- Admin authentication required

## Filter Examples

### Example 1: Monthly Campaign Performance
```
Campaign: "Help Student Education"
Period: Last 30 Days
```
Shows all donations and metrics for the selected campaign in the last month.

### Example 2: Quarterly Overview
```
Campaign: All Campaigns
Period: Last 90 Days
```
Shows aggregated data for all campaigns in the last quarter.

### Example 3: Custom Date Range
```
Campaign: "Medical Emergency Fund"
Period: Custom Range
Start Date: 2024-01-01
End Date: 2024-12-31
```
Shows full year performance for specific campaign.

### Example 4: Today's Activity
```
Campaign: All Campaigns
Period: Today
```
Shows real-time daily performance across all campaigns.

## Benefits

### For Administrators
- **Better Decision Making**: Data-driven insights for campaign management
- **Performance Tracking**: Monitor individual campaign success
- **Donor Analysis**: Identify top contributors and engagement patterns
- **Trend Analysis**: Understand donation patterns over time

### For Reporting
- **Professional Exports**: CSV format for further analysis
- **Print-Ready**: Clean, professional printed reports
- **Flexible Timeframes**: Any period analysis needed
- **Detailed Metrics**: Comprehensive data points

### For Campaign Management
- **Individual Insights**: Deep dive into specific campaigns
- **Progress Monitoring**: Track towards goals
- **Donor Engagement**: Understand supporter behavior
- **Timeline Analysis**: See donation patterns over time

## Future Enhancements (Potential)

1. **PDF Export**: Direct PDF generation with charts
2. **Email Reports**: Scheduled report delivery
3. **Comparison View**: Compare multiple campaigns side-by-side
4. **Forecasting**: Predict campaign completion dates
5. **Donor Segmentation**: Advanced donor analytics
6. **Geographic Analysis**: Location-based insights
7. **Payment Method Analysis**: Breakdown by payment type
8. **Goal Tracking**: Progress alerts and notifications

## Troubleshooting

### No Data Showing
- Check if filters are too restrictive
- Verify campaign has donations in selected period
- Clear filters and try again

### Charts Not Loading
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify Chart.js library is loading

### Export Not Working
- Check browser pop-up blocker
- Ensure JavaScript is enabled
- Try different browser if issues persist

## Support

For issues or questions:
1. Check filter settings are correct
2. Verify data exists for selected criteria
3. Clear browser cache if charts not updating
4. Contact system administrator for technical issues

