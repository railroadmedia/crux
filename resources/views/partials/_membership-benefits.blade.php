@if($membershipDetailsSubView === 'trial-offer')
    <p>You’re eligible for a free 7-day trial to get:</p>
@else
    <p>Drumeo gives you access to:</p>
@endif

<ul>
    <li>Drumeo Method step-by-step curriculum.</li>
    <li>200+ courses from legendary teachers.</li>
    <li>Entertaining shows and documentaries.</li>
    <li>Song breakdowns & Play-Alongs.</li>
    <li>Weekly live lessons and personal support.</li>
</ul>

@if($showCancelMembershipButton)
    {{-- Note: this is *not* the cancel button, but it *does* shows whenever the cancel button does. That's why we use "$showCancelMembershipButton" --}}

    {{-- todo: make open modal --}}
    <p>Click here if you’d like help getting the most out of your account.</p>
@endif