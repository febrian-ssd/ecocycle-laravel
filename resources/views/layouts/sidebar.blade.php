<div class="sidebar">
    @if(Auth::user()->isAdmin)
        <button onclick="window.location.href='/data-user'">Data User</button>
        <button onclick="window.location.href='/data-dropbox'">Data Dropbox</button>
        <button onclick="window.location.href='/riwayat-scan'">Riwayat Scan</button>
    @endif
    <button onclick="window.location.href='/logout'">Logout</button>
</div>
