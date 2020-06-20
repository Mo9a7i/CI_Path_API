# Codeigniter Model for Path.com API

:warning: This library is considered `deprecated` as of now since Path APP has been decomissioned since 2018.

A simple Wrapper model to interact with Path.com API

## USAGE

* _(Optional)_ Update the constructor of the model, read through comments to figure out what is needed.
 
* Initiate the model when needed:
```PHP
$this->load->model('path_model');
$this->path_model->authorize("**email address**","**password**");
```

* Then, simply call one of the functions directly like the example below:
```PHP
echo "<pre>"
var_dump($this->path_model->postThought("Hello World"));
```

## Dependencies
+ PHP
+ CodeIgniter
+ CodeIgniter-Curl


## Documentation

* Code is fairly documented, read through, your help in documentation is welcome.


## Authors

**Mohannad Otaibi (Mo9a7i)**
+ [http://twitter.com/BuFai7an](http://twitter.com/BuFai7an)
+ [http://github.com/Mo9a7i](http://github.com/Mo9a7i)
+ [http://MohannadOtaibi.com](http://MohannadOtaibi.com)

**Hengki Sihombing (original source)**
+ [https://github.com/aredo/Path-API](https://github.com/aredo/Path-API)
