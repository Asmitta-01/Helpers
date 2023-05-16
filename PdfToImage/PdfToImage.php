<?php

/**
 * @property ?bool $is_64_bits true if the system is 64 bits, false otherwise and null if the system is not a win os
 * @property bool $is_win_os true if the current system is a windows system and false otherwise
 * @property string $gs_command The ghostscript command, depending on th OS we have gswin64c.exe, gswin32c.exe, gs.
 * 
 * @property string $file_prefix The prefix of the image that will be created, but we can say that it is the name of the file
 */
class PdfToImage
{
    private bool $is_win_os;
    private ?bool $is_64_bits;
    private string $gs_command;
    private string $gs_path;

    const GS_WIN_64 = "gswin64c.exe";
    const GS_WIN_32 = "gswin32c.exe";
    const GS_LINUX = "gs";

    const THUMBNAIL_SM = "100x100";
    const THUMBNAIL_MD = "250x250";
    const THUMBNAIL_LG = "500x500";

    public function __construct(
        private string $pdf_file_path,
        private string $output_folder,
        private string $size = '',
        private string $file_prefix = ""
    ) {
        if (!$this->initSystem()) {
            throw new \Exception("Unable to find GhostScript installation", 404);
        }
    }

    public function createImage()
    {
        // $image_path = $this->output_folder . "/" . $this->file_prefix . basename($this->pdf_file_path, '.pdf') . "." . $this->imageExtension;
        // $output = $this->executeGS("-dSAFER -dBATCH -sDEVICE={$this->imageDeviceCommand} {$this->pngDownScaleFactor} -r{$this->resolution} -dNumRenderingThreads=4 -dFirstPage={$this->page_start} -dLastPage={$this->page_end} -o \"$image_path\" -dJPEGQ={$this->jpeg_quality} -q \"{$this->pdf_path}\" -c quit");

        if (!file_exists($this->pdf_file_path) || is_dir($this->output_folder) === false)
            throw new \Exception('The pdf file or the output folder doesn\'t exists');

        if ($this->file_prefix == "")
            $this->file_prefix = basename($this->pdf_file_path, '.pdf');

        if ($this->size == "")
            $this->size = static::THUMBNAIL_SM;

        $command = "-dBATCH -sDEVICE=png16m -dPDFFitPage -g{$this->size} -o \"{$this->output_folder}\\{$this->file_prefix}.png\" \"{$this->pdf_file_path}\" ";
        $this->execute($this->gs_command . " " . $command);
    }

    public function setOutputPath(string $output_folder)
    {
        $this->output_folder = $output_folder;
    }

    public function setPdfPath(string $file_path)
    {
        $this->pdf_file_path = $file_path;
    }

    public function setFilePrefix(string $file_prefix)
    {
        $this->file_prefix = $file_prefix;
    }

    private function execute(string $command, bool $is_shell = true)
    {
        $output = null;
        if ($is_shell) {
            $output = shell_exec($command);
        } else {
            exec($command, $output);
        }
        return $output;
    }

    private function initSystem(): bool
    {
        $gs_version = -1;
        $this->is_win_os = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($this->is_win_os) {
            if (trim($gs_bin_path = $this->execute("where " . static::GS_WIN_64, true)) != "") {
                $this->is_64_bits = true;
                $this->gs_command = static::GS_WIN_64;
                $this->gs_path = trim(str_replace("bin\\" . $this->gs_command, "", $gs_bin_path));
            } else if (trim($gs_bin_path = $this->execute("where " . static::GS_WIN_32, true)) != "") {
                $this->is_64_bits = false;
                $this->gs_command = static::GS_WIN_32;
                $this->gs_path =  trim(str_replace("bin\\" . $this->gs_command, "", $gs_bin_path));
            }

            if ($this->gs_path && $this->gs_command) {
                $output = $this->execute($this->gs_command . ' --version 2>&1');
                $gs_version = doubleval($output[0]);
            }
        } else {
            $output = $this->execute(static::GS_LINUX . ' --version 2>&1');
            if (!(
                (is_array($output)
                    && (strpos($output[0], 'is not recognized as an internal or external command') !== false)
                )
                || !is_array($output) && trim($output) == ""
            )) {
                $this->gs_command = static::GS_LINUX;
                $gs_version = doubleval($output[0]);
                $this->gs_path = "";
                $this->is_64_bits = null;
            }
        }

        return $gs_version != -1;
    }
}
