<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4f46e5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .project-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            margin-top: 20px;
        }
        .badge {
            background-color: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Proyek Berhasil Dikirim!</h1>
    </div>

    <div class="content">
        <p>Halo <strong>{{ $project->name }}</strong>,</p>
        <p>Terima kasih telah mengirimkan draf penelitian Anda. Kami telah menerima permintaan Anda dengan status <span class="badge">PENDING</span>.</p>

        <div class="project-info">
            <h3 style="margin-top: 0;">Detail Proyek:</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 5px 0; color: #6b7280;">Topik:</td>
                    <td style="padding: 5px 0;"><strong>{{ $project->topic ?? 'Tidak ada topik' }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6b7280;">Deadline:</td>
                    <td style="padding: 5px 0;">{{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; color: #6b7280;">Budget:</td>
                    <td style="padding: 5px 0;">Rp {{ number_format($project->budget, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <p>Tim kami akan segera meninjau draf tersebut dan menghubungi Anda melalui email atau telepon ({{ $project->phone ?? '-' }}) untuk langkah selanjutnya.</p>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/dashboard') }}" style="background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Cek Status di Dashboard</a>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Academic Support System. Seluruh hak cipta dilindungi.</p>
    </div>
</body>
</html>
