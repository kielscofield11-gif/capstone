@page { margin: 14mm 12mm 16mm; }
* { box-sizing: border-box; }
body { margin: 0; font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; line-height: 1.35; color: #263238; }
.report-header { position: relative; min-height: 48px; margin-bottom: 12px; padding: 0 58px 10px; text-align: center; border-bottom: 1.5px solid #1e3a5f; }
.report-logo { position: absolute; left: 0; top: 0; width: 44px; height: 44px; object-fit: contain; }
h1 { margin: 0 0 3px; color: #1e3a5f; font-size: 16px; line-height: 1.2; }
.subtitle { margin: 0; color: #64748b; font-size: 9px; }
.summary { margin: 0 0 9px; padding: 6px 8px; border: 1px solid #dbe3ea; background: #f7fafc; font-size: 9px; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
thead { display: table-header-group; }
tr { page-break-inside: avoid; }
th { padding: 5px; border: 1px solid #18324d; background: #1e3a5f; color: #fff; font-size: 7.5px; line-height: 1.2; text-align: left; text-transform: uppercase; }
td { padding: 4px 5px; border: 1px solid #d9e0e6; vertical-align: top; overflow-wrap: break-word; word-wrap: break-word; }
tbody tr:nth-child(even) { background: #f8fafc; }
.center { text-align: center; } .right { text-align: right; } .nowrap { white-space: nowrap; }
.empty { padding: 16px; color: #64748b; text-align: center; }
.report-footer { position: fixed; right: 0; bottom: -10mm; left: 0; padding-top: 4px; border-top: 1px solid #d9e0e6; color: #718096; font-size: 7px; text-align: center; }
