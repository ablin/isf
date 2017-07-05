// The button to increment the product value
$(document).on('click', '.product_quantity_up', function(e){
    e.preventDefault();
    fieldName = $(this).data('id-product');
    minQ = $(this).data('minimal_quantity');
    
    var currentVal = parseInt($('#quantity_wanted_'+fieldName).val());
        quantityAvailableT = 100000000;
    if (!isNaN(currentVal) && currentVal < quantityAvailableT)
        $('#quantity_wanted_'+fieldName).val(currentVal + 1).trigger('keyup');
    else
        $('#quantity_wanted_'+fieldName).val(currentVal + 1).trigger('keyup');
});

// The button to decrement the product value
$(document).on('click', '.product_quantity_down', function(e){
    e.preventDefault();
   fieldName = $(this).data('id-product');
    minQ = $(this).data('minimal_quantity');
    var currentVal = parseInt($('#quantity_wanted_'+fieldName).val());
    if (!isNaN(currentVal) && currentVal > minQ)
        $('#quantity_wanted_'+fieldName).val(currentVal - 1).trigger('keyup');
    else
        $('#quantity_wanted_'+fieldName).val(minQ);
});