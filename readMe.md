# ajax_api
## What does it do?
It adds AJAX-functionality to your extensions in an easy and convenient way.
There is no need to take care of AJAX request by yourself. Just code your extension and templates as usually – ajax_api will take care of the rest

## Setup
* Install the extension via composer
* Activate the extension in the backend
* The TypoScript-settings of ajax_api will be activated automatically
* Now you need to activate the AJAX-Js via TypoScipt
```
plugin.tx_ajaxapi {
    settings {

        # cat=plugin.tx_ajaxapi//a; type=boolean; label=Include JS for AJAX-API (Version 2)?
        includeAjaxApiJs = 1
    }
}
```
* Don't forget to inherit you regular `page`-object to the `txAjaxApiPage`-object. This should happen after all other settings are done, so that both objects are identical.
```
txAjaxApiPage.10 < page.10
```
* You can test your setup by calling your website in a browser with `?type=250` appended to the URL. This should render your website with all contents, but without CSS or JavaScripts.
```
http://example.local/?type=250
```

## Usage
### Basics
First you will have to extend your `ActionContoller` from the `AjaxControllerAbstract`. This way the AJAX- functionality is added to your extension.
 ```
 class YourController extends \Madj2k\AjaxApi\Controller\AjaxAbstractController
 {
    [...]
 }
 ```
Now that the basic functionality is available we can use it.
This is best explained using an example.
### Example
Let's pretend you have an extension which:
 * renders a list of elements
 * a filter for this list
 * a more-button for pagination

The Fluid-code may look like this:
```
<div class="filters">
    <select name="filter1">
        <option value="1">Value 1</option>
        <option value="2">Value 2</option>
    </select>
</div>
<div class="list">
    <div class="list__inner">
        <div class="list__item">
            Item 1
        </div>
        <div class="list__item">
            Item 3
        </div>
        <div class="list__item">
            Item 3
        </div>
    </div>
    <div class="button">
        <a href="#">More</a>
    </div>
</div>
```

So we probably want to archive the following behavior:
* If the user filters the items of the list, the whole list should be refreshed via AJAX.
* If the user clicks the more button, further items should be added to the list via AJAX.
* In both cases the more-button should be updated via AJAX in order to have the correct page number as param.

In order to archive this behavior we have to use the AjaxWrapper-ViewHelper. This ViewHelper marks the sections in your HTML that are to be used for Ajax. With it you can also define which action should take place.
The AjaxWrapper-ViewHelper expects the following params:
* ajaxId: This is the internal ID you define in order to distinguish the sections you use from each other. You don't have to take care for namespaces. Just make sure each ID is numeric and is used only once across all your templates, partials and layouts.
* ajaxAction: Here you define what will happen with the code inside the ViewHelper-Tag when loaded via Ajax. If you set the value to "replace" it  will replace existing content, if you set the value to "append" it will be added at the end of the existing content, and if you set the value to "prepend" it will be added before the existing content.
* ajaxHelper: Well, here you simply set the AjaxHelper-Object.

First, lets take a look at the changed code before we try to explain why we did it this way:

```
<div class="filters">
    <form>
        <select name="filter1">
            <option value="1">Value 1</option>
            <option value="2">Value 2</option>
        </select>
        <button type="submit">Send</button>
    <form>
</div>
<ajaxApi:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="1">
    <div class="list">
        <ajaxApi:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="append" ajaxId="2">
            <div class="list__inner">
                <div class="list__item">
                    Item 1
                </div>
                <div class="list__item">
                    Item 3
                </div>
                <div class="list__item">
                    Item 3
                </div>
            </div>
        </ajaxApi:ajaxWrapper>

        <ajaxApi:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="3">
            <div class="button">
                <a href="#">More</a>
            </div>
        </ajaxApi:ajaxWrapper>
    </div>
</ajaxApi:ajaxWrapper>
```

This will be code rendered to the frontend like this:
```
<div class="filters">
    <form>
        <select name="filter1">
            <option value="1">Value 1</option>
            <option value="2">Value 2</option>
        </select>
        <button type="submit">Send</button>
    <form>
</div>
<div class="list" id="773bc02ea02b903280d609bb6a883735afbd7f14-1" data-tx-ajax-api-id="1" data-tx-ajax-api-action="replace">
    <div class="list__inner" id="773bc02ea02b903280d609bb6a883735afbd7f14-2" data-tx-ajax-api-id="2" data-tx-ajax-api-action="append">
        <div class="list__item">
            Item 1
        </div>
        <div class="list__item">
            Item 3
        </div>
        <div class="list__item">
            Item 3
        </div>
    </div>
    <div class="button" id="773bc02ea02b903280d609bb6a883735afbd7f14-3" data-tx-ajax-api-id="3" data-tx-ajax-api-action="replace">
        <a href="#">More</a>
    </div>
</div>
 ```
 As you can see the AjaxWrapper-ViewHelpers will add some attributes to their first child elements. Note that these attributes will only be added to a defined set of valid HTML-tags (e.g. DIVs and FORMs).
 What did we do? We told the extension to:
 * Completely refresh the innerHTML of the DIV with the id "773bc02ea02b903280d609bb6a883735afbd7f14-1" - **BECAUSE**: If the user filters the items of the list, the whole list should be refreshed via AJAX.
 * Add items to the innerHTML of the DIV with the id "773bc02ea02b903280d609bb6a883735afbd7f14-2" - **BECAUSE:** If the user clicks the more button, further items should be added to the list via AJAX.
 * Completely refresh the innerHTML of the DIV with id "773bc02ea02b903280d609bb6a883735afbd7f14-3" - **BECAUSE:** In both cases the more button should be updated via AJAX in order to have the correct page number as param.

What is still missing is an information about when these actions are to be executed.
This is done by setting some params to the links and forms with the additionalParams-attribute. You can combine the following params with custom params for your extension.
 ```
additionalParams="{your_stuff: '{key: value}', ajax_api: '{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \'2,3\'}'}"
 ```
Let's take a look on the params and what they do:
* key: Just add `ajaxHelper.key` here. This is a internal generated key that is meant to prevent collisions between extensions and plugins.
* cid: Just add `ajaxHelper.contentUid` here. This is the uid of the current content element in order to include flexform settings.
* idl: This is the magical part. Here you add a list of Ajax-Ids that are to be updated via Ajax.

Let's integrate that into our example:
```
<div class="filters">
    <f:form noCacheHash="true" action="list" controller="More" extensionName="Example" pluginName="Morecontent" class="ajax"
        additionalParams="{ajax_api : '{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \'1\'}'">
        <select name="filter1">
            <option value="1">Value 1</option>
            <option value="2">Value 2</option>
        </select>
        <button type="submit">Send</button>
    </f:form>
</div>
<ajaxApi:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="1">
    <div class="list">
        <ajaxApi:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="append" ajaxId="2">
            <div class="list__inner">
                <div class="list__item">
                    Item 1
                </div>
                <div class="list__item">
                    Item 3
                </div>
                <div class="list__item">
                    Item 3
                </div>
            </div>
        </ajaxApi:ajaxWrapper>

        <ajaxApi:ajaxWrapper ajaxHelper="{ajaxHelper}" ajaxAction="replace" ajaxId="3">
            <f:link.action action="list" controller="More" extensionName="Example" pluginName="Morecontent"
                class="ajax"
                title="<f:translate key='partials.default.more.boxes.more.labelMore' extensionName='Example' />"
                rel="nofollow" target="_blank"
                additionalParams="{tx_example_morecontent: '{pageNumber: pageNumber, filter: filter}', ajax_api : '{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \'2,3\'}'}">
                    <f:translate key="partials.default.more.boxes.more.labelMore" extensionName="Example" />
            </f:link.action>
        </ajaxApi:ajaxWrapper>
    </div>
</ajaxApi:ajaxWrapper>
 ```

This way the FORM with the param `idl=1` will take care of the AjaxWrapper with `id=1` and thus replace all the innerHTML of the first DIV when submitted.
Therefore if the user filters the items, he will get a new list and a new more button.

The more- link with param `idl=2,3` will take care of the AjaxWrapper with `id=2` and `id=3`, when clicked.
In case of the AjaxWrapper with `id=2` the new content will be added to the innerHTML of the first DIV (e. g. the second page of the list).
In case of the AjaxWrapper with `id=3` the button itself will be replaced with an new one, which now contains the correct params to load the third page.

Lust but not least: don't forget to add the CSS-class `ajax` to all elements that are meant to trigger AJAX-events ;-)

That is basically all the magic.


### Advanced usage options
#### On Submit
By default, a form is always sent "on change". This behavior can be changed to "on submit" by using the additional class "ajax-submit-only"
```
<f:form
    action="new"
    class="ajax ajax-submit-only"
    additionalParams="{ajax_api: '{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \'1\'}'}"
>
```

#### Scroll to top
To scroll to top after submit use class "ajax-scroll-top"

#### Loading indicator
As data-attribute you can specify the ID of the container that should be grayed out with "data-ajax-indicator-id"

### Special Case I: Execute Ajax-Call on page-load
To achieve this you simply can add a template-tag to the website. The following example additionally checks for logged in users an ONLY executes the ajax call when one is logged in
```
<template class="ajax" id="tx-example-login-info-ajax"></template>
<f:comment><!-- only do an ajax-call if fe-cookie is set. This is to reduce requests to the server--></f:comment>
<script type="text/javascript">
    var txExampleAjaxUrl = "{f:uri.action(action:'loginInfo', absolute:'1', addQueryString: '1', additionalParams:'{ajax_api : \'{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \\\'1\\\'}\'}') -> f:format.raw()}";
    if (document.cookie.indexOf('fe_typo_user=') > -1) {
        document.getElementById('tx-example-login-info-ajax').setAttribute('data-ajax-url', txExampleAjaxUrl);
    }
</script>
 ```

If you combine this with a form, you can additionally check whether the form was submitted. This way, the ajax call is only triggered, when the form was not submitted.
In that use-case it is also important to use the forward-method in case of an error in the controller, because the forward-methods keeps the POST-vars and thus prevents a further ajax-call.
 ```
 <template class="ajax" id="tx-example-login-info-ajax"></template>
 <f:comment><!-- only do an ajax-call if fe-cookie is set AND the form was not submitted. This is to reduce requests to the server--></f:comment>
 <f:if condition="! {ajaxHelper.isPostCall}">
     <script type="text/javascript">
         var txExampleAjaxUrl = "{f:uri.action(action:'loginInfo', absolute:'1', addQueryString: '1', additionalParams:'{ajax_api : \'{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \\\'1\\\'}\'}') -> f:format.raw()}";
         if (document.cookie.indexOf('fe_typo_user=') > -1) {
             document.getElementById('tx-example-login-info-ajax').setAttribute('data-ajax-url', txExampleAjaxUrl);
         }
     </script>
 </f:if>
  ```
### Special Case II: Execute Ajax-Call on page-load with specific viewport only
In some cases it is helpful to bind the ajax-request on page-load to a specific viewport, e.h. to only trigger it on mobile devices.
To archive this you can simply add the corresponding attribute `data-ajax-max-width`.
```
<template class="ajax" data-ajax-max-width="1280" data-ajax-url="{f:uri.action(action:'mobileMenu', absolute:'1', additionalParams:'{ajax_api : \'{key: ajaxHelper.key, cid: ajaxHelper.contentUid, idl: \\\'1\\\'}\'}') -> f:format.raw()}"></template>
 ```

### Additional hints
Since version v8.7.57-stable all requests are sent as "POST" to avoid the JS error "HTTP Error 414. The request URL is too long"
