<?php

namespace DeptOfScrapyardRobotics\Displays\ST77xx\Concerns;

use GeneralPurposeIO\IntegratedCircuits\DataRegister;

/**
 * Keeps width, height and the x / y offsets describing what transmit() addresses when MADCTL changes.
 *
 * The glass sits still in controller RAM; MADCTL changes how column (CASET) and row (RASET) addresses reach it.
 * MX mirrors the column address and MY the row address, then MV exchanges them. So a panel window found at
 * physical column c0 (pw wide) and physical row r0 (ph tall) in RAM of ram_cols × ram_rows is addressed as:
 *
 *   MV off: x = MX ? ram_cols - pw - c0 : c0    width  = pw
 *           y = MY ? ram_rows - ph - r0 : r0    height = ph
 *   MV on:  x = MX ? ram_rows - ph - r0 : r0    width  = ph
 *           y = MY ? ram_cols - pw - c0 : c0    height = pw
 *
 * The configured width, height, offsets and MADCTL describe one orientation together; a later MADCTL write moves
 * all four to the new one. A panel that fills its controller's RAM keeps zero offsets in every orientation.
 */
trait ST77xxOrientation
{
    /** @return array{0: int, 1: int} controller RAM as [columns, rows], MADCTL clear */
    abstract protected static function ramGeometry(): array;

    protected function reorient(DataRegister $from, DataRegister $to): void
    {
        $config = $this->config();
        [$ram_cols, $ram_rows] = static::ramGeometry();
        [$width, $height] = [$config->get('width'), $config->get('height')];
        [$x, $y] = [$config->get('x_offset'), $config->get('y_offset')];

        // Back to the glass: where the window sits in RAM under the old MADCTL.
        if ($from->pixel_direction_vertical) {
            [$pw, $ph] = [$height, $width];
            $r0 = $from->right_left_column_addresses ? $ram_rows - $ph - $x : $x;
            $c0 = $from->bottom_top_row_addresses ? $ram_cols - $pw - $y : $y;
        } else {
            [$pw, $ph] = [$width, $height];
            $c0 = $from->right_left_column_addresses ? $ram_cols - $pw - $x : $x;
            $r0 = $from->bottom_top_row_addresses ? $ram_rows - $ph - $y : $y;
        }

        // And out again under the new one.
        if ($to->pixel_direction_vertical) {
            [$width, $height] = [$ph, $pw];
            $x = $to->right_left_column_addresses ? $ram_rows - $ph - $r0 : $r0;
            $y = $to->bottom_top_row_addresses ? $ram_cols - $pw - $c0 : $c0;
        } else {
            [$width, $height] = [$pw, $ph];
            $x = $to->right_left_column_addresses ? $ram_cols - $pw - $c0 : $c0;
            $y = $to->bottom_top_row_addresses ? $ram_rows - $ph - $r0 : $r0;
        }

        $config->set('width', $width);
        $config->set('height', $height);
        $config->set('x_offset', $x);
        $config->set('y_offset', $y);
    }
}
