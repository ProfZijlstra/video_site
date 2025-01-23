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

    public function nextIndex($course, $block, $day, $part): string
    {
        // get the index of the last file and add one
        // probably better than counting the files and adding one
        $idx = '00';
        chdir("res/course/{$course}/{$block}/lecture/{$day}/{$part}/");
        $files = glob('*.pdf');
        if (count($files) > 0) {
            $last = $files[count($files) - 1];
            $parts = explode('_', $last);
            $full = $parts[0];
            $parts = explode('.', $full);
            $idx = $parts[1];

            $idx += 1;
            if ($idx < 10) {
                $idx = '0'.$idx;
            }
        }
        chdir('../../../../../../../');

        return $idx;
    }

    public function addPdf($course, $block, $day, $part, $file, $title): bool
    {
        $chunks = explode('_', $part);
        $major = $chunks[0];
        $minor = $this->nextIndex($course, $block, $day, $part);
        $idx = $major.'.'.$minor;

        $name = $idx.'_'.$title.'.pdf';
        $ch = chdir("res/course/{$course}/{$block}/lecture/{$day}/{$part}/");
        if (! $ch) {
            return false;
        }
        $move = move_uploaded_file($file, $name);
        chdir('../../../../../../');

        return true;
    }
}
