<x-profile :sharedData="$sharedData" doctitle="{{ $sharedData['username']}}'s follower ">
    @include('profile-follower-only');
</x-profile>
