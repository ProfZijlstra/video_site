<?php

/**
 * PDF Dao Class
 *
 * @author mzijlstra 30 Aug 2024
 *
 * This isn't a traditional DAO as it gets info from the filesystem
 */
#[Repository]
class PdfDao
{
    /**
     * Gets all the PDF files for a given day (lecture)
     *
     * @param  string  $course_num  like cs401
     * @param  string  $block  like 2024-10
     * @param  string  $day  like 'W1D1'
     * @return array data structure about the PDF files
     */
    public function forDay($course_num, $block, $day): array
    {
        $result = [];
        $ch = chdir("res/course/{$course_num}/{$block}/lecture/{$day}/");
        $parts = glob('*', GLOB_ONLYDIR);
        foreach ($parts as $part) {
            $deep = chdir($part);
            $files = glob('*.[pP][dD][fF]'); // pdf files
            $latest = array_pop($files);
            if (! $latest) {
                if ($deep) {
                    chdir('..');
                }

                continue;
            }
            $chunks = explode('_', $part);
            $result[$chunks[0]] = [
                'type' => 'pdf',
                'file' => $latest,
                'duration' => 0,
                'parts' => explode('_', $latest),
            ];
            if ($deep) {
                chdir('../');
            }
        }
        if ($ch) {
            chdir('../../../../../../');
        }

        return $result;
    }
}
