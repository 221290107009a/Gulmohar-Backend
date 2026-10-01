import tinymce from "tinymce";

export default function (options = {}) {
    tinymce.baseURL = `${FleetCart.baseUrl}/build/assets/tinymce`;

    tinymce.init({
        selector: ".wysiwyg",
        theme: "silver",
        height: 350,
        menubar: false,
        branding: false,
        image_advtab: true,
        automatic_uploads: true,
        media_alt_source: false,
        media_poster: false,
        relative_urls: false,
        remove_script_host: false,
        document_base_url: window.location.origin + '/',
        toolbar_mode: "sliding", // Possible values: floating, sliding, scrolling, wrap
        directionality: FleetCart.rtl ? "rtl" : "ltr",
        cache_suffix: `?v=${FleetCart.version}`,
        content_style: "body { color: #333333; }",
        // Allow all image attributes to be preserved
        extended_valid_elements: "img[src|alt|title|width|height|loading|decoding|class|id|style|data-*]",
        plugins:
            "lists, link, table, image, media, paste, autosave, autolink,quickbars, wordcount, code, fullscreen",
        toolbar:
            "styleselect | bold italic underline strikethrough blockquote | bullist numlist | alignleft aligncenter alignright alignjustify | outdent indent | forecolor removeformat | table | image media link | code fullscreen",
        quickbars_selection_toolbar:
            "bold italic | quicklink h2 h3 blockquote quickimage quicktable",
        images_upload_handler(blobInfo, success, failure) {
            let formData = new FormData();

            formData.append("file", blobInfo.blob(), blobInfo.filename());

            $.ajax({
                method: "POST",
                url: route("admin.media.store"),
                data: formData,
                processData: false,
                contentType: false,
            })
                .then((file) => {
                    // Return complete URL with base path
                    let fullUrl = file.path.startsWith('http') 
                        ? file.path 
                        : `${window.location.origin}${file.path}`;
                    console.log('Media upload - file.path:', file.path);
                    console.log('Media upload - fullUrl:', fullUrl);
                    success(fullUrl);
                })
                .catch((xhr) => {
                    failure(xhr.responseJSON.message);
                });
        },
        ...options,
    });

    return tinymce;
}
