<x-profile :sharedData="$sharedData" doctitle="{{ $sharedData['username'] }}'s Profile'">
    @include('profile-post-only')
</x-profile>
