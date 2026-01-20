
@if($errors->any())
<div class="alert alert-danger">
    <h3>Error Occured!</h3>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="form-group">
    <label for="">Title</label>
    <x-form.input 
        label="Title" 
        name="title" 
        class="form-control-lg" 
        role="input"  
        :value="old('title',$post?->title)" 
    />
</div>

<div class="form-group">
    <label for="">content</label>
    <x-form.textarea name="content" >
       {{ old('content', $post?->content) }}
    </x-form.textarea>
</div>

<div class="form-group">
    <label for="">Status</label>
    <div>
        <x-form.radio 
            name="status" 
            :checked="old('status', $post?->status)" 
            :options="[
                'published' => 'published', 
                'archived' => 'Archived'
                ]" 
            />
    </div>
</div>

{{-- upload new images --}}
<div class="form-group">
    <x-form.label id="image">Image</x-form.label>
    <x-form.input 
        type="file" 
        name="image[]" 
        accept="image/*"  
        multiple
        id="imagesInput"
    />
    {{-- alt new images --}}
    <div id="new-images-alt"></div>

     @if($post && $post->images->count())
        <div class="mt-3">
            @foreach($post->images as $index => $image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $image->image) }}" height="60">

                    <label>Alt</label>
                    <x-form.input
                        name="existing_image_alt[{{ $image->id }}]"
                        :value="old('existing_image_alt.' . $image->id, $image->alt)"
                    />
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="form-group">
    <button 
        type="submit" 
        class="btn btn-primary">
            {{ $button_label ?? 'Save' }}
    </button>
</div>

@push('script')
    <script>
        document.getElementById('imagesInput').addEventListener('change', function () {
            const container = document.getElementById('new-images-alt');
            container.innerHTML = '';

            Array.from(this.files).forEach((file) => {
                container.innerHTML += `
                    <div class="mt-2">
                        <label>Alt for ${file.name}</label>
                        <input
                            type="text"
                            name="image_alt[]"
                            class="form-control"
                            placeholder="Alt text"
                        >
                    </div>
                `;
            });
        });
    </script>
@endpush
