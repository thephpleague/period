---
layout: default
title: Release Notes
redirect_from:
    - /changelog/
    - /upgrading/
    - /upgrading/changelog/
---

# Release Notes

These are the release notes from `Period`. We've tried to cover all changes, including backward compatible breaks from 3.0 through to the current stable release. If we've missed anything, feel free to create an issue, or send a pull request.

{% for release in site.github.releases %}
## {{ release.name }} - {{ release.published_at | date: "%Y-%m-%d" }}
{{ release.body | replace:'```':'~~~' | markdownify }}
{% endfor %}