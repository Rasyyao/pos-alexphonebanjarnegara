<claude-mem-context>
# Memory Context

# [alexphonebanjarnegara] recent context, 2026-06-22 3:25pm GMT+7

Legend: 🎯session 🔴bugfix 🟣feature 🔄refactor ✅change 🔵discovery ⚖️decision
Format: ID TIME TYPE TITLE
Fetch details: get_observations([IDs]) | Search: mem-search skill

Stats: 50 obs (17,543t read) | 334,034t work | 95% savings

### Jun 14, 2026
S27 Capital Distribution Tracker Implemented in cashflow.blade.php (Jun 14 at 11:23 AM)
S28 Stock Add Bug — setVal Fallback Empty String Prevented Form Submission (Jun 14 at 11:42 AM)
### Jun 15, 2026
S30 Harga Modal Removal — Partial Completion Status Across Report Views (Jun 15 at 6:38 PM)
### Jun 20, 2026
S31 Decided to Remove Aset Bulan Lalu and Peningkatan Aset from Asset Excel Report (Jun 20 at 2:25 PM)
S32 Harga Beli (Purchase Price) Fully Removed from All UI Views (Jun 20 at 2:32 PM)
S33 Bug fix: Indonesian money input fields saving wrong values (100 juta stored as 1 juta) — root cause found and fixed in edit forms (Jun 20 at 11:18 PM)
S34 Accessories Verify Table Replaced "Harga Jual" Column with "Inputter" (Date + Time) (Jun 20 at 11:31 PM)
### Jun 21, 2026
S35 Superadmin Sales Auto-Approval Implemented in SaleService (Jun 21 at 12:10 AM)
288 12:23a 🟣 Superadmin Sales Auto-Approval Implemented in SaleService
S37 FinanceService.php Confirmed Exists and Syntax-Valid During Laba Bersih Debug (Jun 21 at 12:23 AM)
289 12:32a ⚖️ Role-Based Daily Sales Report Edit Access Control — Tutup Buku Policy
290 12:33a 🔵 Existing Role Gates in Sales Views — Delete Already Superadmin-Only, Edit Not Date-Gated
360 6:05p 🟣 Estimasi Margin Hidden from Admin, Split Payment Edit Fix Requested
361 6:07p 🔵 Split Payment Architecture and Estimasi Margin Role-Gating — Pre-Edit Research
362 " 🟣 Estimasi Margin Hidden from Admin Role in Sales Show Page
363 " 🔴 Split Payment Fields Now Editable in Unit Edit Form
364 6:08p 🟣 Role-Based Estimasi Margin Visibility in Sales Show Page
365 " 🔴 Split Payment Edit Fixed in Units Edit Form
366 6:09p 🟣 Estimasi Margin Hidden from Admin Role in Sales Show View
367 " 🔴 Split Payment Edit Fixed in Unit Edit View
368 6:10p 🟣 Role-Based Estimasi Margin Visibility + Split Payment Edit Fix
369 " 🔵 UpdateUnitRequest::rules() Crashes with "Attempt to read property 'id' on string"
370 6:11p 🟣 Estimasi Margin Hidden from Admin, Visible to Superadmin in Sales Show Page
371 " 🔴 Split Payment Edit Fixed in units/edit.blade.php
372 6:12p 🟣 Estimasi Margin Hidden from Admin, Visible to Superadmin in Sales Show Page
373 " 🔴 Split Payment Fields Now Editable in Unit Edit Form
374 " 🟣 Estimasi Margin Hidden from Admin, Visible to Superadmin in Sales Show
375 " 🔴 Split Payment Edit Fields Now Editable in units/edit.blade.php
376 6:44p 🟣 Sales Show Page — Split Payment Detail Display & Edit Requested
377 " 🔵 Sales Module Architecture — Split Payment Schema & Edit Constraints Traced
378 6:45p 🟣 Sales Show Page — Split Payment Edit Support Requested
379 6:46p 🟣 Sales Show Page — Split Payment Display & Edit Feature Requested
380 6:48p 🔵 sales/show.blade.php — Split Selling Price Display and Edit Feature Request
381 6:49p 🟣 Sales Show Page — Split Payment Detail Display & Edit Request
382 " 🔵 SaleController::update() Crashes on null toDateString() — Line 84 Bug Found
383 6:50p 🟣 Sales Show Page — Split Payment Display & Edit Support Requested
384 6:51p 🔴 SaleController Guards Against Split Payment Changes on Partially-Paid Debts
385 6:56p ✅ Laba Bersih Hidden from Admin Role in Finance Report
386 6:57p 🟣 Laba Bersih Hidden from Admin Role in Finance Report
387 " ✅ Laba Bersih Hidden from Admin Role in Finance Report
389 7:03p 🔵 Debt Payment Recording Architecture — Current System Investigation
390 " 🔵 Finance Report Bug — Debt Payments Not Showing for Previous-Day Buyers
391 7:05p 🔵 Finance Report Missing Debt Payment Records — Timing & Recording Bug
392 7:08p 🔴 Finance Report Daily Income Misses Debt Payments From Prior-Day Sales
393 7:09p 🔵 Finance Report Missing Debt Payment Records — Same-Day vs Previous-Day Issue
394 " 🔵 Finance Report Missing Debt Payment Records for Unreconciled Prior-Day Buyers
396 7:19p 🔵 Finance Report — Net Profit (Laba Bersih) Still Shows Negative Value
397 " 🔵 FinanceService.php Confirmed Exists and Syntax-Valid During Laba Bersih Debug
398 7:22p 🔵 Finance Report — Two Bugs Found in Tutup Buku (Book Closing) Flow
399 7:23p 🔵 Finance Report Tutup Buku — Two Role-Based Access Bugs Identified
### Jun 22, 2026
400 1:45p ⚖️ Cashflow Report Should Exclude Stock Purchase Expenses
401 1:46p ⚖️ Cashflow Report Excludes Stock Purchases — Operational Expenses Only
402 " ⚖️ Cashflow Report Excludes Stock Purchases — Operational Expenses Only
403 1:47p ⚖️ Cashflow Report — Exclude Stock Purchases, Show Only Operational Expenses
404 " 🔴 FinanceService — HP Stock Purchases Removed from Expense Calculations
405 1:48p ⚖️ Cashflow Report — Stock Purchases Excluded from Pengeluaran (Expenses)
406 " ⚖️ Cashflow Report — Exclude Stock Purchases from Pengeluaran (Expenses)
407 2:00p 🔴 HP Unit Purchase Costs Double-Counted in Finance Reports — Removed from FinanceService
408 2:01p 🟣 HP Purchases Added to Finance PDF Report Data
S38 HP Purchases Added to Finance PDF Report Data (Jun 22 at 2:01 PM)

Access 334k tokens of past work via get_observations([IDs]) or mem-search skill.
</claude-mem-context>