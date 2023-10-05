<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="/rent/all">Car Rental</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                @if (session("account_role") == "A")
                    <li class="nav-item">
                        <a class="nav-link" href="/rent/all">Home</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" href="/rent/approval">Approval</a>
                </li>
            </ul>
        </div>
        <a href="{{ url('logout') }}">
            <button class="btn btn-danger" type="button">
                Log Out
            </button>
        </a>
    </div>
</nav>
