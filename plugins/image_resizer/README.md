Image Resizer
=============

You will notice that in the Dinkly config.yml file under plugins that the image_resizer plugin comes activated with your our-of-the-box Dinkly install.  Here is that section of the config:

```
# From /config/config.yml
image_resizer:
    apps:
        image:
            app_name: Handler
            is_plugin: true
            base_href: /image
            enabled: true
            default_module: handler
            files_directory: uploads
```

Image resizer is designed to allow you to specify in the template the dimensions you want the image to serve at and the resizer creates a resized version of the image on the fly and then utilizes that resized version going forward.  In the template you would put something like:

```
<img src="/image/handler/image/cropped/false/w/500/h/315/file_name/my_image.png">
```

This would create a file named cropped-500-315-my_image.png inside of the /uploads/resized/ folder.  This can be a great time saver for frontend responsive development allowing you to drop the orginal file into the uploads folder and simply specifying the various sizes needed in each template.