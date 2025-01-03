
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
        $files = glob('*_on', GLOB_ONLYDIR);
        $result = [];
        foreach ($files as $file) {
            $chunks = explode('_', $file);
            $result[$chunks[0]] = $chunks[1];
        }
        if ($ch) {
            chdir('../../../../../../');
        }

        return $result;
    }

    public function add($course, $block, $day, $title): string|bool
    {
        // find max sequence / index
        $ch = chdir("res/course/{$course}/{$block}/lecture/{$day}/");
        if (! $ch) {
            return false;
        }

        $parts = glob('*_on', GLOB_ONLYDIR);
        $last = array_pop($parts);
        $idx = explode('_', $last)[0];
        $idx++;
        if ($idx < 10) {
            $idx = '0'.$idx;
        }
        // create new directory
        $new = "{$idx}_{$title}_on";
        $mk = mkdir($new);
        chdir('../../../../../../');

        if ($mk) {
            return $idx;
        }

        return false;
    }

    public function updateTitle($course, $block, $day, $file, $title): bool
    {
        $parts = explode('_', $file);
        $parts[1] = $title;
        $upd = implode('_', $parts);

        $ch = chdir("res/course/{$course}/{$block}/lecture/{$day}/");
        if (! $ch) {
            return false;
        }
        $ren = rename($file, $upd);
        chdir('../../../../../../');

        return $ren;
    }

    public function delete($course, $block, $day, $file): bool
    {
        $ch = chdir("res/course/{$course}/{$block}/lecture/{$day}/");
        if (! $ch) {
            return false;
        }

        // delete by turning off
        $chunks = explode('_', $file);
        $new_file = '99_'.$chunks[1].'_off';
        $ren = rename($file, $new_file);

        // decrement the sequence of all files after this one
        $files = glob('*_on');
        foreach ($files as $f) {
            $parts = explode('_', $f);
            if ($parts[0] > $chunks[0]) {
                $parts[0]--;
                if ($parts[0] < 10) {
                    $parts[0] = '0'.$parts[0];
                }
                $new = implode('_', $parts);
                rename($f, $new);
            }
        }
        chdir('../../../../../../');

        return $ren;
    }

    public function reorder($course, $block, $day, $ids): bool
    {
        $ch = chdir("res/course/{$course}/{$block}/lecture/{$day}/");
        if (! $ch) {
            return false;
        }

        $files = glob('*_on');
        $ordered = [];
        foreach ($files as $file) {
            $parts = explode('_', $file);
            $ordered[$parts[0]] = $file;
        }

        foreach ($ids as $idx => $id) {
            if (! isset($ordered[$id])) {
                echo 'Missing file for id '.$id;
                chdir('../../../../../../');

                return false;
            }
            $file = $ordered[$id];
            $parts = explode('_', $file);
            $new_id = intval($idx) + 1;
            if ($new_id < 10) {
                $new_id = '0'.$new_id;
            }
            $parts[0] = $new_id;
            rename($file, implode('_', $parts));
        }
        chdir('../../../../../../');

        return true;
    }
}
