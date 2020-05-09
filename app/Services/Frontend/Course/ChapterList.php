<?php

namespace App\Services\Frontend\Course;

use App\Caches\CourseChapterList as CourseChapterListCache;
use App\Models\Course as CourseModel;
use App\Models\User as UserModel;
use App\Repos\Course as CourseRepo;
use App\Services\Frontend\CourseTrait;
use App\Services\Frontend\Service;

class ChapterList extends Service
{

    use CourseTrait;

    public function handle($id)
    {
        $course = $this->checkCourse($id);

        $user = $this->getCurrentUser();

        $this->setCourseUser($course, $user);

        return $this->getChapters($course, $user);
    }

    protected function getChapters(CourseModel $course, UserModel $user)
    {
        $cache = new CourseChapterListCache();

        $chapters = $cache->get($course->id);

        if (empty($chapters)) {
            return [];
        }

        if ($user->id == 0) {
            foreach ($chapters as &$chapter) {
                foreach ($chapter['children'] as &$lesson) {
                    $lesson['me'] = [
                        'owned' => $this->ownedCourse || $lesson['free'] ? 1 : 0,
                        'progress' => 0,
                    ];
                }
            }
        } else {
            $mappings = $this->getLearningMappings($course, $user);
            foreach ($chapters as &$chapter) {
                foreach ($chapter['children'] as &$lesson) {
                    $lesson['me'] = [
                        'owned' => $this->ownedCourse || $lesson['free'] ? 1 : 0,
                        'progress' => $mappings[$lesson['id']]['progress'] ?? 0,
                    ];
                }
            }
        }

        return $chapters;
    }

    protected function getLearningMappings(CourseModel $course, UserModel $user)
    {
        $courseRepo = new CourseRepo();

        $userLearnings = $courseRepo->findUserLearnings($course->id, $user->id);

        if ($userLearnings->count() == 0) {
            return [];
        }

        $mappings = [];

        foreach ($userLearnings as $learning) {
            $mappings[$learning['chapter_id']] = [
                'progress' => $learning['progress'],
            ];
        }

        return $mappings;
    }

}
