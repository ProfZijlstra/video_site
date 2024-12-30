
<?php

/**
 * Parts Dao Class (For lesson parts)
 *
 * @author mzijlstra 29 Dec 2024
 *
 * This isn't a traditional DAO as it gets info from the filesystem
 */
#[Repository]
class LessonPartDao
{
    /**
     * Gets all the lesson parts for a given day (lecture)
     *
     * @param  string  $course_num  like cs401
     * @param  string  $block  like 2024-10
     * @param  string  $day  like 'W1D1'
     * @return array data with all the parts
     */
    public function forDay($course_num, $block, $day): array
    {
        $ch = chdir("res/course/{$course_num}/{$block}/lecture/{$day}/");
        $files = glob('*', GLOB_ONLYDIR);
        $result = [];
        foreach ($files as $file) {
            $chunks = explode('_', $file);
            if (count($chunks) >= 2) {
                $result[$chunks[0]] = $chunks[1];
            }
        }
        if ($ch) {
            chdir('../../../../../../');
        }

        return $result;
    }
}
