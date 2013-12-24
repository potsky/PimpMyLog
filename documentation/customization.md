---
layout: default
title: Customization
---

# Table display

## Table headers

```html
<th class="Date">Date<span class="glyphicon glyphicon-chevron-down"></span></th>
```

## Table columns

```html
<td title="Raw value" class="pml-Severity pml-badge">Formatted value</td>
```

# Customize CSS

This stylesheet is located at /css/config.inc.css
Your stylesheet will be loaded instead of this one if you create a file located at /css/config.inc.user.css


```css

/* For logs table header */
.logs th {
        text-align: center;
}

/* For all logs table cells */
.logs td {
        font-size: 0.8em;
}

/* Specific to cell types */
.pml-pre,
.pml-txt,
.pml-link {
        font-size: 0.8em;
        white-space: pre-wrap;
        -ms-word-break: break-all;
        word-break: break-all;
        word-break: break-word;
        -webkit-hyphens: auto;
        -moz-hyphens: auto;
        hyphens: auto;
}
...

/* Specific to column */
.pml-Severity {
        font-size: 1em;
}

.pml-Code {
        text-align: center;
}

.pml-Referer {
        min-width: 100px;
}

.pml-UA {
  font-size: 0.8em;
}
```
