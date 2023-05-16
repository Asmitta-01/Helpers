# Pdf To Image

I made this class to create an image from a pdf, a conversion. I use it to create thumbnails of my pdf files.

> :warning: You need to install [GhostScript](https://www.ghostscript.com/) (>= 10.0.1) to use this class.

## Example

Here is an example of use:

```php
<?php

require_once "./PdfToImage.php";

$pdf_path = 'path/to/the/pdf/file/';
$output = 'path/to/the/output/folder';

$pTI = new PdfToImage($pdf, $output);
$pTI->createImage();

?>
```

This code will create an image file with the same name as the pdf file, but different extension(pdf -> png) in the `$output` folder.

Create an [issue](https://github.com/Asmitta-01/Helpers/issues) if you have any question or suggestion.
