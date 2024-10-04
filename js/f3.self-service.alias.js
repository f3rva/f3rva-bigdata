$(document).ready(function() {

    $('#primaryMemberName').autocomplete({
        lookup: members,
        onSelect: function (suggestion) {
            $('#primaryMemberId').val(suggestion.data);
            $('#primaryMemberName').val(suggestion.value);
            $('#aliasAlert').hide();
        },
        onSearchComplete: function (query, suggestions) {
            $('#primaryMemberId').val('');
        }
    });

    $('#aliasMemberName').autocomplete({
        lookup: members,
        onSelect: function (suggestion) {
            $('#aliasMemberId').val(suggestion.data);
            $('#aliasMemberName').val(suggestion.value);
            $('#aliasAlert').hide();
        },
        onSearchComplete: function (query, suggestions) {
            $('#aliasMemberId').val('');
        }
    });

    // add validation to prevent form submission if either the primaryMemberId or aliasMemberId is empty
    $('#aliasForm').submit(function() {
        // check for empty required fields
        if ($('#primaryMemberId').val() === '' || 
            $('#aliasMemberId').val() === ''
        ) {
            $('#aliasAlert').html('Please select a primary and alias member.');
            $('#aliasAlert').show();
            return false;
        } 
        else if (
            $('#primaryMemberId').val() === $('#aliasMemberId').val()
        ) {
            $('#aliasAlert').html('Primary and alias members cannot be the same.');
            $('#aliasAlert').show();
            return false;
        }
    });

});