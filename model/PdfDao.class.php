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
        chdir("res/course/{$course_num}/{$block}/lecture/{$day}/pdf/");
        $files = glob('*.[pP][dD][fF]'); // pdf files
        $result = [];
        foreach ($files as $file) {
            $info = pathinfo($file);
            $parts = explode('_', $info['filename']);
            $result[$parts[0]] = [
                'type' => 'pdf',
                'file' => $file,
                'duration' => 0,
                'parts' => explode('_', $info['filename']),
            ];
        }
        chdir('../../../../../../../');

        return $result;
    }
}
