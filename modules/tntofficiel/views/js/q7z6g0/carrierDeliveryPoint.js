/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */


// On DOM Ready.
$(function () {

    // If not an order with TNT carrier.
    if (window.TNTOfficiel.link.back && !TNTOfficiel.order.isTNT) {
        return;
    }

    if (
        // If Google Map API not loaded.
    !(window.google && window.google.maps && window.google.maps.Map)
    // and Google Map config exist.
    && (window.TNTOfficiel && window.TNTOfficiel.config && window.TNTOfficiel.config.google && window.TNTOfficiel.config.google.map)
    ) {
        // Load Google Map API.
        var objJqXHR = TNTOfficiel_AJAX({
            "url": window.TNTOfficiel.config.google.map.url,
            "data": window.TNTOfficiel.config.google.map.data,
            "dataType": 'script',
            "cache": true
        });

        objJqXHR
        .done(function () {
            // Script loaded.
        });
    }

});



// Constructor
function TNTOfficiel_GMapMarkersConstrutor(elmtMapContainer, objGoogleMapsConfig) {
    return this.init(elmtMapContainer, objGoogleMapsConfig);
}

// Prototype
TNTOfficiel_GMapMarkersConstrutor.prototype = {

    // Google Map Default Config.
    objGMapsConfig: {
        "lat": 46.227638,
        "lng": 2.213749,
        "zoom": 4
    },

    // Google Map Object.
    objGMapMap: null,
    // Google Map Markers Area Boundaries.
    objGMapMarkersBounds: null,
    // Google Map Markers Collection.
    arrGMapMarkersCollection: [],
    // Google Map Markers Info Window (Bubble).
    objGMapMarkersInfoWindow: null,

    /**
     * Initialisation.
     */
    init: function init(elmtMapContainer, objGoogleMapsConfig) {
        // Extend Configuration.
        jQuery.extend(this.objGMapsConfig, objGoogleMapsConfig);

        // Google Map Object.
        this.objGMapMap = new window.google.maps.Map(elmtMapContainer, {
            center: new window.google.maps.LatLng(this.objGMapsConfig.lat, this.objGMapsConfig.lng),
            zoom: this.objGMapsConfig.zoom
        });

        // Init Markers.
        this.objGMapMarkersBounds = new window.google.maps.LatLngBounds();
        this.arrGMapMarkersCollection = [];
        this.objGMapMarkersInfoWindow = new window.google.maps.InfoWindow();

        return this;
    },
    /**
     *
     */
    addMarker: function addMarker(fltLatitude, fltLongitude, strURLIcon, strInfoWindowContent, objListeners) {
        var _this = this;

        var objGMapLatLng = new window.google.maps.LatLng(fltLatitude, fltLongitude);

        // Create a new Google Map Marker.
        var objGMapMarker = new window.google.maps.Marker({
            position: objGMapLatLng,
            icon: strURLIcon
        });
        // Add Marker to the Google Map.
        objGMapMarker.setMap(this.objGMapMap);
        // Extend Markers Area Boundaries.
        this.objGMapMarkersBounds.extend(objGMapMarker.getPosition() /* objGMapLatLng */);

        //objGMapMarker.getMap();

        // Bind Markers Events.
        jQuery.each(objListeners, function (strEventType, evtCallback) {
            // If callback is a function.
            if (jQuery.type(evtCallback) === 'function') {
                // Set Marker Event Listeners and Bind this to callback.
                objGMapMarker.addListener(strEventType, $.proxy(function (objGMapEvent) {
                    // Default click action is to show InfoWindow (if any).
                    if (strEventType === 'click') {
                        this.objGMapMarkersInfoWindow.close();
                        if (strInfoWindowContent) {
                            this.objGMapMarkersInfoWindow.setContent(strInfoWindowContent);
                            this.objGMapMarkersInfoWindow.open(this.objGMapMap /* objGMapMarker.getMap() */, objGMapMarker);
                        }
                        // Adjust zoom min/max range.
                        objGMapMarker.map.setZoom(Math.max(Math.min(17, objGMapMarker.map.getZoom()),10));
                        // Update the Google Maps size.
                        this.trigger('resize', this.objGMapMap);
                        // Go to marker position.
                        objGMapMarker.map.panTo(objGMapMarker.getPosition());
                    }

                    return evtCallback.call(this, objGMapEvent);
                }, _this));
            }
        });

        // Add Marker to collection.
        this.arrGMapMarkersCollection.push(objGMapMarker);

        return objGMapMarker;
    },
    /**
     *
     */
    fitBounds: function () {
        // Fit Boundaries to display all markers.
        if (this.arrGMapMarkersCollection.length > 0) {
            this.objGMapMap.fitBounds(this.objGMapMarkersBounds);
        }

        // Bind event to callback to execute only once.
        window.google.maps.event.addListenerOnce(this.objGMapMap, 'bounds_changed', function () {
            // this === this.objGMapMap
            this.setZoom(Math.min(17, this.getZoom()));
        });

        // Update the Google Maps size.
        this.trigger('resize', this.objGMapMap);

        return this;
    },
    /**
     *
     */
    trigger: function (strEventType, objBind) {
        window.google.maps.event.trigger(objBind, strEventType);

        return this;
    }
};


/**
 *
 * @param strArgCarrierCode
 * @param strDataB64
 * @constructor
 */
var TNTOfficiel_deliveryPointsBox = function (strArgCarrierCode, strDataB64)
{
    var _this = this;

    // xett, pex
    this.strRepoType = (strArgCarrierCode === 'DROPOFFPOINT') ? 'xett' : 'pex';
    // relay_points, repositories.
    this.method = (strArgCarrierCode === 'DROPOFFPOINT') ? 'relay_points' : 'repositories';
    // ClassName Plural Prefix.
    this.strClassNameRepoPrefixPlural = (strArgCarrierCode === 'DROPOFFPOINT') ? 'relay-points' : 'repositories';
    // ClassName Prefix.
    this.strClassNameRepoPrefix = (strArgCarrierCode === 'DROPOFFPOINT') ? 'relay-point' : 'repository';

    this.strClassNameInfoBlocSelected = 'is-selected';

    this.CSSSelectors = {
        // Popin Content Container.
        // div#repositories.repositories
        popInContentContainer: '#' + this.method,
        // Popin Header Container.
        // div.repositories-header
        popInHeaderContainer: '.' + this.strClassNameRepoPrefixPlural + '-header',

        // Search form CP/Cities.
        // form#repositories_form.repositories-form
        formSearchRepo: 'form.' + this.strClassNameRepoPrefixPlural + '-form',
        // Repo List Container.
        // ul#repositories_list.repositories-list
        infoBlocListContainer: '.' + this.strClassNameRepoPrefixPlural + '-list',
        // Google Map Container.
        // div#repositories_map.repositories-map
        mapContainer: '.' + this.strClassNameRepoPrefixPlural + '-map',

        // All Repo Bloc Item Container.
        // li#repositories_item_<INDEX>.repository-item
        infoBlocItemContainerCollection: '.' + this.strClassNameRepoPrefix + '-item',
        // Repo Selected Button Bloc Item.
        // button.repository-item-select
        infoBlocItemButtonSelected: '.' + this.strClassNameRepoPrefix + '-item-select'

        // One Repo Bloc Item Container.
        // li#repositories_item_<CODE>.repository-item
        //    '#' + this.method + '_item_' + id
    };

    // getRelayPoints, getRepositories List.
    this.arrRepoList = JSON.parse(TNTOfficiel_inflate(strDataB64)) || null;

    // If invalid repo list.
    if ($.type(this.arrRepoList) !== 'array') {
        // Set empty repo list.
        this.arrRepoList = [];
    }

    // Auto submit on change.
    $(this.CSSSelectors.formSearchRepo).find(':input').not(':submit').on('change', function () {
        var strPostCode = $('#tnt_postcode').val();
        if ($.type(strPostCode) === 'string' && strPostCode.length === 5) {
            $(this).parents('form').first().submit();
        } else {
            // Disable form select, waiting content is loaded.
            $('#tnt_city').prop('disabled', true);
        }
    });

    // On repo form search submit.
    $(this.CSSSelectors.formSearchRepo).on('submit', function (objEvent) {

        $htmlForm = $(this);

        objEvent.preventDefault();

        // Get state.
        var boolPostCodeChanged = false;
        $('#tnt_postcode').each(function (intIndex, element) {
            if (element.getAttribute('value') !== $(element).val()) {
                boolPostCodeChanged = true;
            }
        } );

        if (boolPostCodeChanged) {
            // Disable form input city, waiting content is loaded.
            $('#tnt_city').prop('disabled', true);
        }

        var objLink = window.TNTOfficiel.link.front;
        var arrData = $htmlForm.serializeArray();
        if (window.TNTOfficiel.link.back) {
            objLink = window.TNTOfficiel.link.back;
            arrData.push({name: 'id_order', value:  TNTOfficiel.order.intOrderID});
        }
        var strData = $.param(arrData);

        // If test is valid, get all data for postcode and city and update the list.
        var objJqXHR = TNTOfficiel_AJAX({
            "url": objLink.module[_this.strRepoType === 'xett' ? 'boxRelayPoints' : 'boxDropOffPoints'],
            "method": 'GET',
            "data": strData,
            "dataType": 'html',
            "cache": false
        });

        // Disable all form input, waiting content is loaded.
        $htmlForm.find(':input').prop('disabled', true);

        objJqXHR
        .done(function (mxdData, strTextStatus, objJqXHR) {
            // Update PopIn Content.
            $(_this.CSSSelectors.popInContentContainer).parent().html(mxdData);
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            window.location.reload();
        });

    });

    // On select button repo info bloc click.
    $(this.CSSSelectors.infoBlocItemButtonSelected).on('click', function () {
        var intMarkerIndex = $(this).parents(_this.CSSSelectors.infoBlocItemContainerCollection).attr('id').split('_').pop();

        var objLink = window.TNTOfficiel.link.front;
        var objData = {
            "product": TNTOfficiel_deflate($.param(_this.arrRepoList[intMarkerIndex]))
        };
        if (window.TNTOfficiel.link.back) {
            objLink = window.TNTOfficiel.link.back;
            objData['id_order'] = TNTOfficiel.order.intOrderID;
        }

        var objJqXHR = TNTOfficiel_AJAX({
            "url": objLink.module.saveProductInfo,
            "method": 'POST',
            "data": objData,
            "dataType": 'html',
            "cache": false
        });

        objJqXHR
        .done(function (mxdData, strTextStatus, objJqXHR) {
            $.fancybox.close();

            TNTOfficiel_ShowPageSpinner();
            if (window.TNTOfficiel.link.back) {
                window.location.reload();
            } else {
                // Delete existing Repository Info.
                $('.shipping-method-info').remove();
                TNTOfficiel_updateCarrier();
            }
        })
        .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
            window.location.reload();
        });
    });


    this.eventGoogleMaps = function () {
        // If Google library available.
        if (window.google && window.google.maps) {
            var objTNTOfficielGMapMarkers = new TNTOfficiel_GMapMarkersConstrutor(
                $(_this.CSSSelectors.mapContainer)[0],
                window.TNTOfficiel.config.google.map.default
            );

            // Prepare and returns data marker to add on the map.
            for (var intMarkerIndex = 0; intMarkerIndex < _this.arrRepoList.length; intMarkerIndex++) {

                var objRepoItem = _this.arrRepoList[intMarkerIndex];

                // Set Marker InfoWindow Content.
                var strInfoWindowContent = '\
                    <ul style="margin: 0;">\
                      ' + (this.strRepoType == 'xett' ? '<li>Code: <b>' + objRepoItem[this.strRepoType] + '</b></li>' : '') + '\
                      <li><b>' + objRepoItem.name + '</b></li>\
                      <li>' + (objRepoItem.address ? objRepoItem.address : objRepoItem.address1 + '<br />' + objRepoItem.address2) + '</li>\
                      <li>' + objRepoItem.postcode + ' ' + objRepoItem.city + '</li>\
                    </ul>';

                var strCSSSelectorRepoInfoBloc = '#' + _this.method + '_item_' + intMarkerIndex;

                var objGMapMarker = objTNTOfficielGMapMarkers.addMarker(
                    objRepoItem.latitude,
                    objRepoItem.longitude,
                    window.TNTOfficiel.link.front.shop + 'modules/' + window.TNTOfficiel.module.name + '/' + 'views/img/' + 'map/marker/' + (intMarkerIndex + 1) + '.png',
                    strInfoWindowContent,
                    {
                        // On Marker Click.
                        "click": $.proxy(function (strCSSSelectorRepoInfoBloc, objGMapEvent) {
                            var strClassNameInfoBlocSelected = 'is-selected',
                                $elmtInfoBlocSelect = $(strCSSSelectorRepoInfoBloc);

                            // Highlight Selected Marker Info.
                            $(_this.CSSSelectors.infoBlocItemContainerCollection + '.' + strClassNameInfoBlocSelected)
                            .removeClass(strClassNameInfoBlocSelected);
                            $elmtInfoBlocSelect.addClass(strClassNameInfoBlocSelected);

                            // The event is the click on marker (not triggered from list).
                            if (objGMapEvent != null) {
                                // Scroll to item
                                _this.scrollY($elmtInfoBlocSelect);
                            }
                        }, null, strCSSSelectorRepoInfoBloc)
                    }
                );

                // On click on info bloc item, trigger click on marker.
                $(strCSSSelectorRepoInfoBloc).off().on('click', $.proxy(function (objGMapMarker) {
                    objTNTOfficielGMapMarkers.trigger('click', objGMapMarker);
                }, null, objGMapMarker));

            }

            objTNTOfficielGMapMarkers.fitBounds();
        }

    };

    /**
     * Prepare scrollbar for list item
     * @private
     */
    this.prepareScrollbar = function () {
        $('#list_scrollbar_container').nanoScroller({
            preventPageScrolling: true
        });
    };

    // Scroll to item.
    this.scrollY = function ($elmtInfoBlocSelect) {
        var $elmtContainer = $('#list_scrollbar_container'),
            $elmtContent = $('#list_scrollbar_content'),
            intPositionItem = parseInt($elmtInfoBlocSelect.offset().top + $elmtContent.scrollTop() - $elmtContainer.offset().top);
        $elmtContent.scrollTop(intPositionItem);
    };

    this.displayUpdate = function () {
        this.prepareScrollbar();
        this.eventGoogleMaps();
    };

    // Update !
    this.displayUpdate();

    return this;
};


/**
 * Open a Fancybox to choose a delivery point (for the products concerned).
 *
 * @param $elmtArgInputRadioVirtTNTChecked
 */
function TNTOfficiel_XHRBoxDeliveryPoints(strTNTClickedCarrierCode)
{
    strTNTClickedCarrierCode = strTNTClickedCarrierCode.split('_')[1];

    var objOpt = {
        "DROPOFFPOINT":'boxRelayPoints',
        "DEPOT":'boxDropOffPoints'
    };

    if (!(strTNTClickedCarrierCode in objOpt)) {
        return;
    }

    var objLink = window.TNTOfficiel.link.front;
    var objData = {};
    if (window.TNTOfficiel.link.back) {
        objLink = window.TNTOfficiel.link.back;
        objData['id_order'] = TNTOfficiel.order.intOrderID;
    }

    var objJqXHR = TNTOfficiel_AJAX({
        "url": objLink.module[objOpt[strTNTClickedCarrierCode]],
        "method": 'POST',
        "dataType": 'html',
        "data": objData
    });

    objJqXHR
    .done(function (strHTMLDeliveryPointsBox, strTextStatus, objJqXHR) {
        if (strHTMLDeliveryPointsBox) {
            if (!!$.prototype.fancybox) {
                $.fancybox.open([
                    {
                        "type": 'inline',
                        "autoScale": true,
                        "autoDimensions": true,
                        "centerOnScroll": true,
                        "maxWidth": 1280,
                        "maxHeight": 768,
                        "fitToView": false,
                        "width": '100%',
                        "height": '100%',
                        "autoSize": false,
                        "closeClick": false,
                        "openEffect": 'none',
                        //"closeEffect": 'none',
                        "wrapCSS": 'fancybox-bgc',
                        "content": strHTMLDeliveryPointsBox,
                        "afterShow": function () {
                            // global object from AJAX HTML Content.
                            objTNTOfficiel_deliveryPointsBox.displayUpdate();
                        },
                        "onUpdate": function () {
                            objTNTOfficiel_deliveryPointsBox.displayUpdate();
                        },
                        "helpers": {
                            "overlay": {
                                "locked" : true,
                                "closeClick": false // prevents closing when clicking OUTSIDE fancybox.
                            }
                        }
                    }
                ], {
                    "padding": 10
                });
            }
        }
    })
    .fail(function (objJqXHR, strTextStatus, strErrorThrown) {
        window.location.reload();
    });
}
