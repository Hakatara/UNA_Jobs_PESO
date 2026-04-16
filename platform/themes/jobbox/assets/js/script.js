$(() => {
    'use strict'

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('.select-location').select2({
        minimumInputLength: 0,
        tags: true,
        ajax: {
            url: $(this).data('url') || (window.siteUrl + '/ajax/locations'),
            dataType: 'json',
            delay: 500,
            type: "GET",
            data: function (params) {
                return {
                    k: params.term, // search term
                    page: params.page || 1,
                    type: $(this).data('location-type'),
                };
            },
            processResults: function (data, params) {
                return {
                    results: $.map(data.data[0], function (item) {
                        return {
                            text: item.name,
                            id: item.name,
                            data: item
                        };
                    }),
                    pagination: {
                        more: (params.page * 10) < data.total
                    }
                };
            }
        }
    });

    $('.location-custom-fields').find('.select2').select2({
        minimumInputLength: 0,
    });

    $('.job-category').select2({
        minimumInputLength: 0,
        ajax: {
            url: $(this).data('url') || (window.siteUrl + '/ajax/categories'),
            dataType: 'json',
            delay: 250,
            type: 'GET',
            data: function (params) {
                return {
                    k: params.term, // search term
                    page: params.page || 1
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: $.map(data.data[0], function (item) {
                        return {
                            text: item.name,
                            id: item.id,
                            data: item
                        };
                    }),
                    pagination: {
                        more: (params.page * 10) < data.data.total
                    }
                };
            }
        }
    });
})
