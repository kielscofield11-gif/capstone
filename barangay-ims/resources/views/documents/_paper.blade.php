<article class="document-paper">
    <header class="document-header">
        @if(data_get($rendered, 'options.show_logo') && $rendered['logo_path'])
            <img class="document-logo" src="{{ asset('storage/'.$rendered['logo_path']) }}" alt="Configured barangay logo">
        @endif
        @foreach(['header_line_1','header_line_2','header_line_3','office_name'] as $line)
            @if($rendered[$line])<div>{!! $rendered[$line] !!}</div>@endif
        @endforeach
    </header>
    <h1>{!! $rendered['title'] !!}</h1>
    @if(data_get($rendered, 'options.show_resident_photo') && $rendered['resident_photo_path'])
        <img class="resident-photo" src="{{ asset('storage/'.$rendered['resident_photo_path']) }}" alt="Resident photo">
    @endif
    @if($rendered['opening_phrase'])<p class="opening">{!! nl2br($rendered['opening_phrase']) !!}</p>@endif
    <div class="document-body">{!! nl2br($rendered['body']) !!}</div>
    @if($rendered['closing_text'])<div class="document-closing">{!! nl2br($rendered['closing_text']) !!}</div>@endif
    <div class="document-meta">
        @if(data_get($rendered, 'options.show_control_number'))<span>Control No.: {{ $rendered['meta']['control_number'] }}</span>@endif
        @if(data_get($rendered, 'options.show_fee'))<span>Fee: {{ $rendered['meta']['fee'] }}</span>@endif
        @if(data_get($rendered, 'options.show_issue_date'))<span>Issue date: {{ $rendered['meta']['issue_date'] }}</span>@endif
    </div>
    <div class="signature">
        <div class="signature-line">{!! $rendered['signatory_name'] ?: '&nbsp;' !!}</div>
        <div>{!! $rendered['signatory_position'] !!}</div>
    </div>
    @if($rendered['footer_text'])<footer>{!! nl2br($rendered['footer_text']) !!}</footer>@endif
</article>
