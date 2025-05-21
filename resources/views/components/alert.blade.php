@if(session()->has($type))
<div class="a'alert alert-{{type}}">
        {{session($type)}}
</div>
@endif
