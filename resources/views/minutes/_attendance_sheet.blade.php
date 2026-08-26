<div style="font-family:'nunito',sans-serif;">
    <div style="text-align:center;line-height:1.5">
        <div>Republic of the Philippines</div>
        <div>Province of Sorsogon</div>
        <div style="font-weight:bold">MUNICIPALITY OF PRIETO DIAZ</div>
        <div>Sangguniang Bayan</div>
    </div>
    <h3 style="text-align:center;text-transform:uppercase;letter-spacing:1px;margin:16px 0 2px">Attendance Sheet</h3>
    <div style="text-align:center;margin-bottom:14px">{{ $info->category }} No. {{ $info->series_number }}</div>

    <table style="width:100%;font-size:12px;margin-bottom:14px">
        <tr>
            <td><b>Date &amp; Time:</b> {{ date('F d, Y · h:i A', strtotime($info->date_created)) }}</td>
            <td><b>Venue:</b> {{ $info->venue }}</td>
        </tr>
        <tr>
            <td><b>Presiding Officer:</b> {{ $info->presiding_officer }}</td>
            <td><b>Session Type:</b> {{ $info->category }}</td>
        </tr>
    </table>

    <table border="1" cellpadding="6" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:12px">
        <thead>
            <tr style="background:#f0f2f5">
                <th style="width:26px">#</th><th style="text-align:left">Name</th>
                <th style="text-align:left">Position</th><th style="width:80px">Status</th><th style="width:140px">Signature</th>
            </tr>
        </thead>
        <tbody>
            @php $statusLabel = ['P'=>'Present','A'=>'Absent','E'=>'Excused','L'=>'Late']; $i=1; @endphp
            @forelse($attendees as $a)
                <tr>
                    <td style="text-align:center">{{ $i++ }}</td>
                    <td>{{ $a->name }}</td>
                    <td>{{ $a->position }}</td>
                    <td style="text-align:center">{{ $statusLabel[$a->status] ?? $a->status }}</td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center">No roster attendance recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table style="width:100%;font-size:12px;margin-top:12px">
        <tr>
            <td><b>Present:</b> {{ $tally['P'] }}</td>
            <td><b>Late:</b> {{ $tally['L'] }}</td>
            <td><b>Excused:</b> {{ $tally['E'] }}</td>
            <td><b>Absent:</b> {{ $tally['A'] }}</td>
            <td><b>Total roster:</b> {{ $tally['P'] + $tally['A'] + $tally['E'] + $tally['L'] }}</td>
        </tr>
    </table>

    @if(trim($info->attendance ?? '') !== '')
        <div style="font-size:12px;margin-top:12px">
            <b>Guests / Others present:</b><br>
            {!! nl2br(e($info->attendance ?? '')) !!}
        </div>
    @endif
</div>
