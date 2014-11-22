---
layout: default
title: Customization
---

# Introduction

The default stylesheet is located at `css/config.inc.css`. **Do not modify** this file, it will be erased on update!

You can create your own stylesheet which will be loaded **instead of** the default one. Your custom stylesheet has to be located at `css/config.inc.user.css`.

If you have installed *Pimp my Log* with composer, install the custom css file in the root of composer at `css/config.inc.user.css` ! If you install the `css/config.inc.user.css` at the root of *Pimp my Log* in `/vendor/potsky/pimp-my-log/ss/config.inc.user.css`, it will be deleted while the next composer update !!!


> **Note**  
> 
> If you only want to override some default values (and want to have future style updates), you can include the original CSS file in your file and add some style.
> 
> The following example shows you how to disable the new log border color while keeping all default styles. Here is your `config.inc.user.css` file:
> 
> ```css
> /* Load the default stylesheet */
> @import url('config.inc.css');
> 
> /* Override only this setting */
> .newlog td:first-child {
>     background-image:none;
> }
> ```

<!-- -->

# Table headers

## All headers

You can of course define a style for all header columns :

```css
/* For logs table header */
.logs th {
    text-align: center;
}
```

## Specific headers

A header from the logs table looks like this in HTML:

```html
<th class="Date">Date<span class="glyphicon glyphicon-chevron-down"></span></th>
```

The class `Date` is the parameter defined in the `config.user.json` file:

```js
"files": {
    "apache": {
        "format"  : {
            "match": {
                "Date" : 1,
                ...
```

`Date` name is chosen by you during configuration process. So you can define a style for the header of the column *Date* or *what you want* like this:

```css
.logs th.Date {
    text-align: right;
}
```


# Logs

## All columns

You can of course define a style for all logs columns :

```css
/* For all logs table cells */
.logs td {
        font-size: 0.8em;
}
```

## Specific column

The HTML content for a cell looks like this in HTML:

```html
<td title="Raw value" class="pml-Severity pml-badge">Formatted value</td>
```

The name of the column here is `Severity` with type `badge`.

So you can change the style of content of the column `Severity`:

```css
/* Specific to column */
.pml-Severity {
    font-size: 1em;
}
```

And you can of course change the style of all `badge` columns like this:

```css
/* Specific to cell types */
.pml-badge {
    font-size: 1em;
}
```

## Per file type

The HTML content for all rows (headers and logs) is:

```html
<tr class="apache1">...</tr>
```

The current parsed log file ID is defined as a CSS classname so you can customize distinct design according to each log file.

# New logs

When new logs are available, a thin left border is applied on their rows. The default color is *violet*:

{% image /assets/ss/getstarted_desktopnotifications1.png class="img-responsive" alink="" atarget="" %}

<a name="newlogs"></a>

You can replace the color in the CSS code by one of these:

- `1x1blue_low.gif`
- `1x1blue.gif`
- `1x1green_low.gif`
- `1x1green.gif`
- `1x1orange_low.gif`
- `1x1orange.gif`
- `1x1pink_low.gif`
- `1x1pink.gif`
- `1x1red_low.gif`
- `1x1red.gif`
- `1x1yellow_low.gif`
- `1x1yellow.gif`

You can disable this feature by not including this code from the `css/config.inc.css` file:

```css
/* New log color on the row */
.newlog td:first-child {
    background-position: 0 0;
    background-image:url(../img/1x1pink_low.gif);
    background-size: 1px 100%;
    background-repeat: no-repeat;
}
```


