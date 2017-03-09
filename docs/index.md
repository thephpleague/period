---
layout: homepage
---

<header>
    <div class="inner-content">
      <a href="http://thephpleague.com/" class="league">
          Presented by The League of Extraordinary Packages
      </a>
      <h1>{{ site.data.project.title }}</h1>
      <h2>{{ site.data.project.tagline }}</h2>
      <p class="composer"><span>$ composer require league/period</span></p>
    </div>
</header>

<main>
  <div class="example">
    <div class="inner-content">
      <h1>Usage</h1>

<div class="highlighter-rouge"><pre class="highlight"><code><span class="cp">&lt;?php</span>

<span class="nb">date_default_timezone_set</span><span class="p">(</span><span class="s1">'UTC'</span><span class="p">);</span>

<span class="k">use</span> <span class="nx">League\Period\Period</span><span class="p">;</span>

<span class="nv">$period</span> <span class="o">=</span> <span class="nx">Period</span><span class="o">::</span><span class="na">createFromDuration</span><span class="p">(</span><span class="s1">'2014-10-03 08:12:37'</span><span class="p">,</span> <span class="mi">3600</span><span class="p">);</span>
<span class="nv">$start</span> <span class="o">=</span> <span class="nv">$period</span><span class="o">-&gt;</span><span class="na">getStartDate</span><span class="p">();</span>
<span class="nv">$end</span>   <span class="o">=</span> <span class="nv">$period</span><span class="o">-&gt;</span><span class="na">getEndDate</span><span class="p">();</span>
<span class="nv">$duration</span>  <span class="o">=</span> <span class="nv">$period</span><span class="o">-&gt;</span><span class="na">getDateInterval</span><span class="p">();</span>
<span class="nv">$altPeriod</span> <span class="o">=</span> <span class="nv">$period</span><span class="o">-&gt;</span><span class="na">endingOn</span><span class="p">(</span><span class="s1">'2014-12-03'</span><span class="p">);</span>
<span class="nv">$period</span><span class="o">-&gt;</span><span class="na">contains</span><span class="p">(</span><span class="nv">$altPeriod</span><span class="p">);</span> <span class="c1">//return false;
</span><span class="nv">$altPeriod</span><span class="o">-&gt;</span><span class="na">durationGreaterThan</span><span class="p">(</span><span class="nv">$period</span><span class="p">);</span> <span class="c1">//return true;
</span></code></pre>
</div>
    </div>
  </div>


  <div class="highlights">
    <div class="inner-content">
      <div class="column one">
        <h1>Highlights</h1>
        <div class="description">
        <p><code>Period</code> is PHP's missing Time Range class. It is based on <a href="http://verraes.net/2014/08/resolving-feature-envy-in-the-domain/">Resolving Feature Envy in the Domain</a> by Mathias Verraes and extends the concept to cover all basic operations regarding time ranges.</p>
        </div>
      </div>
      <div class="column two">
        <ol>
          <li><p>Treats Time Range as immutable value objects</p></li>
          <li><p>Exposes many named constructors to ease time range creation</p></li>
          <li><p>Covers all basic manipulations related to time range</p></li>
          <li><p>Framework-agnostic</p></li>
        </ol>
      </div>
    </div>
  </div>

  <div class="documentation">
    <div class="inner-content">
      <h1>Releases</h1>
      <div class="version current">
        <h2>Current Stable Release</h2>
        <div class="content">
          <p><code>League\Period 3.0</code></p>
          <ul>
            <li>Requires: <strong>PHP >= 5.5.0</strong></li>
            <li>Release Date: <strong>2015-09-02</strong></li>
            <li>Supported Until: <strong>TBD</strong></li>
          </ul>
          <p><a href="/api/">Full Documentation</a></p>
        </div>
      </div>

      <div class="version legacy">
        <h2>No longer Supported</h2>
        <div class="content">
          <p><code>League\Period 2.0</code></p>
          <ul>
            <li>Requires: <strong>PHP >= 5.3.0</strong></li>
            <li>Release Date: <strong>2014-10-15</strong></li>
            <li>Supported Until: <strong>2016-03-02</strong></li>
          </ul>
        </div>
      </div>

      <p class="footnote">Once a new major version is released, the previous stable release remains supported for six (6) more months through patches and/or security fixes.</p>

    </div>
  </div>

  <div class="questions">
    <div class="inner-content">
      <h1>Questions?</h1>
      <p><strong>League\Period</strong> was created by Ignace Nyamagana Butera. Find him on Twitter at <a href="https://twitter.com/nyamsprod">@nyamsprod</a>.</p>
    </div>
  </div>
</main>