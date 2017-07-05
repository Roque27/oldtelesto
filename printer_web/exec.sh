#!/bin/sh
echo "Comienza comando"
#pdftk /lpd/out/20131203165227179.pdf background /opt/lampp/htdocs/printer_web/preprinted/preimpreso_subsidio_final.pdf output /opt/lampp/htdocs/printer_web/temp/20131203165227179.pdf 
pdftk $@
echo "Finaliza comando"