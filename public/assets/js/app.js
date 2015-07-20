$(function() {
    //Get albums
    $('body').on('click', '.jQChoiceAlbum', function(e) {
        e.preventDefault();
        FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                FB.api("/me/permissions", function (perm) {
                    if (perm.data.length == 2) {
                        FB.login(function(response) { 
                            getFormAlbums();
                        }, {scope:'publish_actions,user_photos'});
                    }else{
                        getFormAlbums();
                    }
                });
            }
        });
    });

    //Get photos
    $('body').on('click', '.jQChoicePhoto', function(e) {
        e.preventDefault();

        var idAlbum = $(this).attr('data-id');

        $modal = $('#modal');
        $modalBody = $modal.find('.modal-body');
        $.get('/api/album/' + idAlbum + '/photos', function(response) {
            var html = '<div class="row">',
                data = response.data;
            $.each(data.photos, function(i, photo) {
                html += '<div class="col-xs-6 col-md-4">' +
                    '<div class="thumbnail photo">' +
                        '<img src="' + photo.source + '" alt="img" class="img-responsive" data-id="'+photo.id+'">' +
                    '</div>' +
                '</div>';
            });
            html += '</div>';
            $modalBody.html(html);
        });
    });

    //Select photo
    $('body').on('click', '.photo img', function(e) {
        e.preventDefault();
        
        $figcaption = $(this).parent().find('.fig');
        console.log($figcaption);
        if ($figcaption.length == 0) { 
            var srcPhoto = $(this).attr('src');
            var idPhoto  = $(this).attr('data-id');
            var html = '<figcaption class="fig">'+
                            '<form action="/api/upload/photo" method="post">'+
                                '<input type="hidden" name="src" value="'+srcPhoto+'">'+
                                '<input type="hidden" name="photo_id" value="'+idPhoto+'">'+
                                '<input type="hidden" name="from" value="facebook">'+
                                '<input type="text" name="title" placeholder="Titre"/>'+
                                '<textarea name="description" placeholder="Description"></textarea>'+
                                '<button type="submit">Valider</button>'
                            '</form>'+
                        '</figcaption>';

            $(this).parent('div').append(html);
        };
    });

    //Get form upload from desktop
    $('body').on('click', '.jQUploadPhoto', function(e) {
        e.preventDefault();
        $modalBody = $('#modal').find('.modal-body');
        $modalBody.html();
        $.get('/api/albums', function(response) {
            var data = response.data;
            var html = '<div class="row">'+
                            '<form class="from-desk" enctype="multipart/form-data" action="/api/upload/photo" method="post">'+
                                '<input type="hidden" name="from" value="desktop">'+
                                '<input type="file" name="file"/>'+
                                '<input type="text" name="title" placeholder="Titre"/>'+
                                '<textarea name="description" placeholder="Description"></textarea>'+
                                '<select name="album_id">'
                                
            ;
            $.each(data.albums, function(i, album) {
                html += '<option value="'+album.id+'">'+ album.name +'</option>'
            });
            html += '</select>'+
                    '<button type="submit">Valider</button>'+
                    '</form>'+
                    '</div>';
            $modalBody.html(html);
        });
    });
    function getFormAlbums() {
        $modal = $('#modal');
        $modalBody = $modal.find('.modal-body');
        $modalBody.html();
        $.get('/api/albums', function(response) {
            var html = '<div class="row">',
                data = response.data;
            $.each(data.albums, function(i, album) {
                html += '<div class="col-xs-6 col-md-4">' +
                    '<div class="thumbnail">' +
                        '<img src="https://graph.facebook.com/' + album.id + '/picture?type=album&access_token=' + data.token + '" alt="img" class="img-responsive jQChoicePhoto" data-id="' + album.id + '">' +
                        '<p>'+ album.name +'</p>' +
                    '</div>' +
                '</div>';
            });
            html += '</div>';
            $modalBody.html(html);
        });
    }
});
