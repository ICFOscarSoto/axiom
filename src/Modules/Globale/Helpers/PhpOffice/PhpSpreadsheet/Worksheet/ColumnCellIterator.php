<?php

namespace App\Modules\Globale\Helpers\PhpOffice\PhpSpreadsheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception as PhpSpreadsheetException;

/**
 * @extends CellIterator<int>
 */
class ColumnCellIterator extends CellIterator
{
    /**
     * Current iterator position.
     *
     * @var int
     */
    private $currentRow;

    /**
     * Column index.
     *
     * @var int
     */
    private $columnIndex;

    /**
     * Start position.
     *
     * @var int
     */
    private $startRow = 1;

    /**
     * End position.
     *
     * @var int
     */
    private $endRow = 1;

    /**
     * Create a new row iterator.
     *
     * @param Worksheet $subject The worksheet to iterate over
     * @param string $columnIndex The column that we want to iterate
     * @param int $startRow The row number at which to start iterating
     * @param int $endRow Optionally, the row number at which to stop iterating
     */
    public function __construct(Worksheet $subject, $columnIndex = 'A', $startRow = 1, $endRow = null)
    {
        // Set subject
        $this->worksheet = $subject;
        $this->columnIndex = Coordinate::columnIndexFromString($columnIndex);
        $this->resetEnd($endRow);
        $this->resetStart($startRow);
    }

    /**
     * (Re)Set the start row and the current row pointer.
     *
     * @param int $startRow The row number at which to start iterating
     *
     * @return $this
     */
    public function resetStart(int $startRow = 1)
    {
        $this->startRow = $startRow;
        $this->adjustForExistingOnlyRange();
        $this->seek($startRow);

        return $this;
    }

    /**
     * (Re)Set the end row.
     *
     * @param int $endRow The row number at which to stop iterating
     *
     * @return $this
     */
    public function resetEnd($endRow = null)
    {
        $this->endRow = $endRow ?: $this->worksheet->getHighestRow();
        $this->adjustForExistingOnlyRange();

        return $this;
    }

    /**
     * Set the row pointer to the selected row.
     *
     * @param int $row The row number to set the current pointer at
     *
     * @return $this
     */
    public function seek(int $row = 1)
    {
        if ($this->onlyExistingCells && !($this->worksheet->cellExistsByColumnAndRow($this->columnIndex, $row))) {
            throw new PhpSpreadsheetException('In "IterateOnlyExistingCells" mode and Cell does not exist');
        }
        if (($row < $this->startRow) || ($row > $this->endRow)) {
            throw new PhpSpreadsheetException("Row $row is out of range ({$this->startRow} - {$this->endRow})");
        }
        $this->currentRow = $row;

        return $this;
    }

    /**
     * Rewind the iterator to the starting row.
     */
    public function rewind(): void
    {
        $this->currentRow = $this->startRow;
    }

    /**
     * Return the current cell in this worksheet column.
     */
    public function current(): ?Cell
    {
        return $this->worksheet->getCellByColumnAndRow($this->columnIndex, $this->currentRow);
    }

    /**
     * Return the current iterator key.
     */
    public function key(): int
    {
        return $this->currentRow;
    }

    /**
     * Set the iterator to its next value.
     */
    public function next(): void
    {
        do {
            ++$this->currentRow;
        } while (
            ($this->onlyExistingCells) &&
            (!$this->worksheet->cellExistsByColumnAndRow($this->columnIndex, $this->currentRow)) &&
            ($this->currentRow <= $this->endRow)
        );
    }

    /**
     * Set the iterator to its previous value.
     */
    public function prev(): void
    {
        do {
            --$this->currentRow;
        } while (
            ($this->onlyExistingCells) &&
            (!$this->worksheet->cellExistsByColumnAndRow($this->columnIndex, $this->currentRow)) &&
            ($this->currentRow >= $this->startRow)
        );
    }

    /**
     * Indicate if more rows exist in the worksheet range of rows that we're iterating.
     */
    public function valid(): bool
    {
        return $this->currentRow <= $this->endRow && $this->currentRow >= $this->startRow;
    }

    /**
     * Validate start/end values for "IterateOnlyExistingCells" mode, and adjust if necessary.
     */
    protected function adjustForExistingOnlyRange(): void
    {
        if ($this->onlyExistingCells) {
            while (
                (!$this->worksheet->cellExistsByColumnAndRow($this->columnIndex, $this->startRow)) &&
                ($this->startRow <= $this->endRow)
            ) {
                ++$this->startRow;
            }
            while (
                (!$this->worksheet->cellExistsByColumnAndRow($this->columnIndex, $this->endRow)) &&
                ($this->endRow >= $this->startRow)
            ) {
                --$this->endRow;
            }
        }
    }
}
