<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload example</title>
    <style>
        .dropzone {
            border: 1px solid #e0e0e0 !important;
        }

        .dropzone .dz-preview .dz-progress {
            top: 70% !important;
            border: 3px solid #28a745 !important;
            background: #28a745d1 !important;
        }

        .dropzone .dz-preview .dz-error-message {
            top: 145px !important;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body>
    <div class="container mt-3">
        <div class="row justify-content-md-center">
            <div class="col-md-12">
                <form id="upload-form">
                    <div id="imageUpload" class="dropzone dz-clickable">
                        <div class="dz-default dz-message"><button class="dz-button" type="button">Choose and Drag file here to upload.</button></div>
                    </div>
                    <div class="form-check form-check-inline mt-3">
                        <input class="form-check-input" type="radio" value="force" name="method" id="flexRadioDefault1" checked>
                        <label class="form-check-label" for="flexRadioDefault1">
                            Force
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" value="max" name="method" id="flexRadioDefault2">
                        <label class="form-check-label" for="flexRadioDefault2">
                            Max
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" value="crop" name="method" id="flexRadioDefault3">
                        <label class="form-check-label" for="flexRadioDefault3">
                            Crop
                        </label>
                    </div>
                    <div class="row g-3 align-items-center mt-1">
                        <div class="col-auto">
                            <label for="width" class="col-form-label">width</label>
                        </div>
                        <div class="col-auto">
                            <input type="text" id="width" class="form-control" name="width" value="1280">
                        </div>
                        <div class="col-auto">
                            <label for="height" class="col-form-label">height</label>
                        </div>
                        <div class="col-auto">
                            <input type="text" id="height" class="form-control" name="height" value="1280">
                        </div>
                        <div class="col-auto">
                            <label for="quality" class="col-form-label">quality</label>
                        </div>
                        <div class="col-auto">
                            <input type="text" id="quality" class="form-control" name="quality" value="100">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary mt-3" id="btn-upload-form">upload</button>
                    </div>
                    <div class="col-md-12 mt-3" id="list-file"></div>
                </form>
            </div>
        </div>
    </div>
    <script>
        var $form = $("#upload-form");
        var myDropzone = null;
        Dropzone.autoDiscover = false;

        $(document).ready(function() {
            initDropzone();
            getListFile(renderList);
        });

        function getListFile(callbackRender) {
            $.ajax({
                type: 'POST',
                url: '<?= (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>get_all_file.php',
                dataType: 'json',
                success: function(rs) {
                    if (rs.status === "success" && rs.list.length > 0) {
                        callbackRender(rs.list);
                    } else {
                        $("#list-file .alert").remove();
                    }
                }
            });
        }

        var tmpArrayCheck = [];

        function initDropzone() {
            if (myDropzone) {
                myDropzone.destroy();
            }
            myDropzone = new Dropzone('div#imageUpload', {
                addRemoveLinks: true,
                autoProcessQueue: false,
                uploadMultiple: true,
                parallelUploads: 100,
                paramName: 'file',
                maxFiles: 10,
                maxFilesize: 2000000,
                clickable: true,
                // Language Strings
                url: '<?= (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>upload.php',
                init: function() {
                    myDropzone = this;
                    $("#btn-upload-form").click(function(e) {
                        e.preventDefault();
                        var fileRow = $(".dz-preview").length;
                        if (fileRow > 0 && fileRow <= 10) {
                            myDropzone.processQueue();
                        } else {
                            if (fileRow < 1) {
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Something went wrong!',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                return false;
                            } else {
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Something went wrong!',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        }
                        return false;
                    });
                    this.on('addedfile', function(file) {

                    });
                    this.on('sending', function(file, xhr, formData) {
                        var data = $form.serializeArray();
                        $.each(data, function(key, el) {
                            formData.append(el.name, el.value);
                        });
                    });
                },
                error: function(file, response) {
                    if ($.type(response) === "string")
                        var message = response;
                    else
                        var message = response.message;
                    file.previewElement.classList.add("dz-error");
                    _ref = file.previewElement.querySelectorAll("[data-dz-errormessage]");
                    _results = [];
                    for (_i = 0,
                        _len = _ref.length; _i < _len; _i++) {
                        node = _ref[_i];
                        _results.push(node.textContent = message);
                    }
                    tmpArrayCheck = [];
                    return _results;
                },
                successmultiple: function(file, response) {
                    console.log($.parseJSON(response), 'successmultiple');
                    let json = $.parseJSON(response);
                    if (json.status === "success") {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Your work has been saved',
                            showConfirmButton: false,
                            timer: 1500
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                // console.log('I was closed by the timer');
                                getListFile(renderList);
                            }
                        });
                    } else {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'Your work has been saved',
                            showConfirmButton: false,
                            timer: 1500
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.timer) {
                                // console.log('I was closed by the timer');
                                getListFile(renderList);
                            }
                        });
                    }
                    tmpArrayCheck = [];
                    this.removeAllFiles(true);
                },
                completemultiple: function(file, response) {
                    console.log(file, response, "completemultiple");
                },
                reset: function() {
                    console.log("resetFiles");
                    $("#imageUpload").removeClass("dz-started");
                    this.removeAllFiles(true);
                },
            });
        }

        function renderList(rs) {
            let html_ = "";
            $.each(rs, function(index, item) {
                html_ += `<div class="alert alert-light" role="alert">
                            <div class="row">
                                <div class="col-md-3">
                                    <a class="icon-link" href="${item.full_path}" target="_blank">
                                        ${item.file_name}
                                    </a>
                                </div>
                                <div class="col-md-3">${item.date}</div>
                                <div class="col-md-2">${item.type}</div>
                                <div class="col-md-2">${item.size}</div>
                                <div class="col-md-2">
                                    <div class="badge text-bg-danger p-2 remove-file" style="cursor:pointer" data-filename="${item.file_name}">remove</div>
                                </div>
                            </div>
                        </div>`;
            });
            $("#list-file").html(html_);
            removeFile();
        }

        function removeFile() {
            $(".remove-file").unbind("click");
            $(".remove-file").click(function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'POST',
                            url: '<?= (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>delete_file.php',
                            data: {
                                'file_name': $(this).data('filename'),
                            },
                            dataType: 'json',
                            success: function(rs) {
                                if (rs.status === "success") {
                                    Swal.fire(
                                        'Deleted!',
                                        'Your file has been deleted.',
                                        'success'
                                    )
                                    getListFile(renderList);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Something went wrong!',
                                    })
                                }
                            }
                        });
                    }
                })
            });
        }
    </script>
</body>

</html>