<?php

/**
 * File Dao Class
 *
 * @author mzijlstra 22 Oct 2024
 *
 * This isn't a traditional DAO as it gets info from the filesystem
 */
#[Repository]
class FileDao
{
    /**
     * Gets the file listing for a given (sub) directory of the course
     *
     * @param  string  $course  like cs401
     * @param  string  $block  like 2024-10
     * @param  string  $dir  like public
     * @return array data structure of dirs and files
     */
    public function getFiles($course, $block, $dir = ''): array
    {
        if (str_contains($dir, '..')) {
            throw new InvalidArgumentException('dir cannot specify parent dir');
        }

        if (! file_exists("res/{$course}/{$block}/public")) {
            mkdir("res/{$course}/{$block}/public");
        }

        $path = "res/{$course}/{$block}/{$dir}";
        $listing = scandir($path);
        $files = [];
        $dirs = [];
        foreach ($listing as $file) {
            if (str_starts_with($file, '.')) {
                continue;
            }

            if (is_dir("{$path}/$file")) {
                $dirs[] = $file;
            } else {
                $files[] = $file;
            }
        }

        return ['dirs' => $dirs, 'files' => $files];
    }

    /**
     * This function clones the public files and is expected to be run after
     * the lectures have been cloned (which creates the directory structure).
     */
    public function clone($course, $new_block, $old_block, $dir = 'public'): void
    {
        $deep = count(explode('/', $dir));
        $depth = implode('/', array_fill(0, $deep, '..'));

        $listing = scandir("res/{$course}/{$old_block}/{$dir}/");
        mkdir("res/{$course}/{$new_block}/{$dir}");
        foreach ($listing as $item) {
            if (is_dir("res/{$course}/{$old_block}/{$dir}/{$item}")) {
                if (str_starts_with($item, '.')) {
                    continue;
                }
                mkdir("res/{$course}/{$new_block}/{$dir}/$item", 0775, true);
                $this->clone($course, $new_block, $old_block, "{$dir}/{$item}");
            } else {
                symlink("../{$depth}/{$old_block}/{$dir}/{$item}",
                    "res/{$course}/{$new_block}/{$dir}/{$item}");
            }
        }
    }

    /**
     * DANGER!
     * Deletes the offering from teh file system.
     *
     * @param  string  $block  the
     */
    public function delete($course, $block): void
    {
        exec("rm -rf res/{$course}/{$block}");
    }
}
