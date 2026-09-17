<?php

/*
| Proven against recording fakes: every command and data byte a panel would
| see, tagged by the DC level it went out under, plus every RST level. Boot
| sequences are pinned to the datasheet init values. The live check is a
| 240×320 ST7789 on an FT232H over MPSSE SPI.
*/
