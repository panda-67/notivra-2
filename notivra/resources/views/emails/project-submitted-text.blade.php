PROYEK BERHASIL DIKIRIM!

Halo {{ $project->name }},

Terima kasih telah mengirimkan draf penelitian Anda. Kami telah menerima permintaan Anda dan saat ini sedang dalam status: PENDING.

--------------------------------------------------
DETAIL PROYEK:
--------------------------------------------------
Topik       : {{ $project->topic ?? 'Tidak ada topik' }}
Deadline    : {{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}
Budget      : Rp {{ number_format($project->budget, 0, ',', '.') }}
Instansi    : {{ $project->institution ?? '-' }}
--------------------------------------------------

Tim kami akan segera meninjau draf tersebut dan menghubungi Anda melalui email ini atau telepon ({{ $project->phone ?? '-' }}) untuk langkah selanjutnya.

Anda juga dapat memantau perkembangan proyek Anda melalui Dashboard kami di:
{{ url('/dashboard') }}

Salam hangat,
Academic Support System
{{ date('Y') }}
