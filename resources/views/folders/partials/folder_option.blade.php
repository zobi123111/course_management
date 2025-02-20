<option value="{{ $folder->id }}">
    {!! str_repeat('&nbsp;&nbsp;&nbsp;', $level) !!} {{ $folder->folder_name }}
</option>

@if($folder->children && $folder->children->count() > 0)
    @foreach($folder->children as $child)
        @include('folders.partials.folder_option', ['folder' => $child, 'level' => $level + 1])
    @endforeach
@endif