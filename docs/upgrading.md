---
layout: default
title: Upgrading and Release Notes
redirect_from:
    - /changelog/
    - /upgrading/changelog/
---

# Upgrading

Welcome to the upgrade guide for `Period`. We've tried to cover all backward compatible breaks from 3.0 through to the current MAJOR stable release. If we've missed anything, feel free to create an issue, or send a pull request. You can also refer to the information found in the [CHANGELOG.md](https://github.com/thephpleague/period/blob/master/CHANGELOG.md) file attached to the library.

# Release Notes

All Notable changes to `Period` will be documented in this file

{% for release in site.github.releases %}
## {{ release.name }} - {{ release.published_at | date: "%Y-%m-%d" }}
{{ release.body | replace:'```':'~~~' | markdownify }}
{% endfor %}