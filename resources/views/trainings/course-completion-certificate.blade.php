<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Course Completion Certificate</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    


@media print {
  .footer-contact,
  .footer-logo {
    display: block !important;
    text-align: center !important;
    margin-top: 10px !important;
  }

  .footer-logo img {
    display: block;
    margin: 0 auto 5px;
  }

  .certificate {
    box-shadow: none !important;
    margin: 0 !important;
    padding: 30mm 20mm !important;
    background: #fff !important;
  }

  body {
    background: none !important;
    padding: 0 !important;
    margin: 0 !important;
  }
}
  </style>
</head>
<body style=" margin: 0; font-family: Arial, sans-serif;">
  <div class="certificate" style="max-width: 800px; background-color: white; padding: 20px 40px 20px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.15); color: #000;">
    <div style="text-align: center; margin-bottom: 10px;">
      @if($event?->orgUnit?->org_logo)
      <img src="{{ public_path('storage/organization_logo/' . $event->orgUnit->org_logo) }}" alt="T8UK Logo" style="max-width: 150px; width: 100%; height: auto;">
      @endif
    </div>
    <h3 style="text-align: center; font-weight: bold; color: #35a1e1; margin-bottom: 0px; padding: bottom 0;">{{ $event->orgUnit?->org_unit_name }} ATO COURSE COMPLETION CERTIFICATE</h3>
    <h4 style="text-align: center; color: #083d5d; font-weight: 600; margin-bottom: 0px; margin: top 0; font-size:20px;">{{ $event->course?->course_name }}</h4>

    <div style="text-align: center; margin: 0px 0 30px;" >
      <p>This is to certify that:</p>
      <h5 style="font-weight: bold; margin: 10px 0;">{{ $student->fname }} {{ $student->lname }}</h5>
      <p>has successfully Completed: <strong style="color: #35a1e1;">PC-12 SET Class Rating:</strong></p>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: #fff;">
      <tbody>
        <tr style="border: 1px solid #dee2e6;">
          <th style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">Name:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">{{ $student->fname }} {{ $student->lname }}</td>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Licence Number:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">{{ $student->documents->licence ?? 'N/A' }}</td>
        </tr>
        <tr>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Training commenced:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">09/11/2023</td>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Training completed:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">07/05/2024</td>
        </tr>
        <tr style="background-color: #f8f9fa;">
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Rating:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">{{ $student->usrRatings->first()?->rating?->name ?? 'N/A' }}</td>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Hours of groundschool:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">(48hrs TK + pre-flight and post-flight briefings)</td>
        </tr>
        <tr>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Training device/s:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">{{ $event->resource->name ?? 'N/A' }}</td>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Theoretical Knowledge Exam Result:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">98%</td>
        </tr>
        <tr style="background-color: #f8f9fa;">
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Reg of device/s:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">{{ $event->resource->registration ?? 'N/A' }}</td>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Date of TK Exam:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">15/11/2023</td>
        </tr>
        <tr>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px;  font-size: 14px;">Hours, flight:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">10hrs 00mins</td>

          <th style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">Hours, simulator:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">2.00 (OTD)</td>
        </tr>
        <tr style="background-color: #f8f9fa;">
          <th style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">Recommended for LST by:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">{{ $event->instructor->fname ?? '' }} {{ $event->instructor->lname ?? '' }}</td>
          <th style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">Licence No:</th>
          <td style="border: 1px solid #dee2e6; padding: 6px 10px; font-size: 14px;">{{ $student->documents->licence ?? 'N/A' }}</td>
        </tr>
      </tbody>
    </table>

    <div style="text-align: center; margin-top: 60px;">
      <img src="images/AvMS_Logo.png" alt="Signature" style="height: 50px; margin-bottom: 5px;">
      <p style="font-weight: 600;">{{ $event->instructor->fname ?? '' }} {{ $event->instructor->lname ?? '' }}</p>
      <p style="color: #6c757d;">Accountable Manager<br>{{ $event->orgUnit?->org_unit_name }}, EASA.GBR.ATO.0447</p>
    </div>

    <div style="font-size: 0.75rem; margin-top: 50px;">
      <p>Part-4, Appendix 10<br>Rev. 1 dated 14<sup>th</sup> November 2023</p>
    </div>

    <div style="width: 100%; margin-top: 30px; font-size: 0.8rem; color: #666; display: table;">
      <!-- Left Side -->
      <div style="display: table-cell; vertical-align: middle; width: 60%;">
        <p style="margin: 0;">
          {{ $event->orgUnit->org_unit_name ?? '' }} LIMITED |
          <a href="mailto:{{ $event->orgUnit->admin->email ?? 'admin@example.com' }}" style="color: #1c3b6f; text-decoration: none; font-weight: 600;">{{ $event->orgUnit->admin->email ?? 'admin@example.com' }}</a> |
          EASA.GBR.ATO.0447
        </p>
      </div>

      <!-- Right Side -->
      @if($event?->orgUnit?->org_logo)
      <div style="display: table-cell; vertical-align: middle; text-align: right; width: 40%;">
        <div style="display: inline-block; text-align: center;">
          <img src="{{ public_path('storage/organization_logo/' . $event->orgUnit->org_logo) }}" alt="T8UK Logo"
              style="max-width: 39px; height: auto; margin-bottom: 5px;">
          <div>
            <a href="mailto:{{ $event->orgUnit->admin->email ?? '#' }}"
              style="color: #1c3b6f; text-decoration: none; font-weight: 600;">
              {{ $event->orgUnit->admin->email ?? 'admin@example.com' }}
            </a>
          </div>
        </div>
      </div>
      @endif
  </div>
</body>
</html>
