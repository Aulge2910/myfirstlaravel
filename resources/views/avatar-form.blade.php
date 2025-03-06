<x-layout doctitle="Manage Your Avatar">
    <form action="/manage-avatar" method="POST" enctype="multipart/form-data"">
        <!-- for form data file type-->
        @csrf
        <div class="">
            <input type="file" name="avatar">
            @error('avatar')
            <p class="alert small alert-danger">{{$message}}</p>
            @enderror
        </div>
        <button type="submit" >save</button>
    </form>
</x-layout>
