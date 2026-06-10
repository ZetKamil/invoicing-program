<nav id="navbar">
  <div class="nav-inner">
    <div class="nav-logo"><a href="{{ url('/') }}">TransDigit<em>.</em></a></div>
    <ul class="nav-links">
      <li><a href="{{ url('/diensten') }}">Diensten & Pakketten</a></li>
      <li><a href="{{ url('/#smartquote') }}">Smart Quote</a></li>
      <li><a href="{{ url('/cases') }}">Realisaties</a></li>
      <li><a href="{{ url('/over-ons') }}">Over ons</a></li>
      <li><a href="{{ url('/blog') }}">Blog</a></li>
      <li><a href="{{ url('/#contact') }}">Contact</a></li>
    </ul>
    <button class="nav-cta" onclick="window.location.href='{{ url('/') }}#audit'">
      Gratis audit →
    </button>
  </div>
</nav>
