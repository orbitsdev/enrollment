<?php

namespace App\Helpers;

use App\Models\User;

class NavigationHelper
{
    /**
     * Get role-filtered navigation groups for the given user.
     *
     * @return array<int, array{label: string, items: array<int, array{title: string, href: string, icon: string}>}>
     */
    public static function getNavigation(User $user): array
    {
        $role = $user->roles->first()?->name;
        $navigation = [];

        // Dashboard - visible to all authenticated users
        $navigation[] = [
            'label' => 'Main',
            'items' => [
                ['title' => 'Dashboard', 'href' => '/dashboard', 'icon' => 'LayoutGrid'],
            ],
        ];

        // Management - admin only
        if ($role === 'admin') {
            $navigation[] = [
                'label' => 'Management',
                'items' => [
                    ['title' => 'Users', 'href' => '/users', 'icon' => 'Users'],
                    ['title' => 'School Settings', 'href' => '/school-settings', 'icon' => 'Settings'],
                    ['title' => 'School Years', 'href' => '/school-years', 'icon' => 'CalendarDays'],
                ],
            ];
        }

        // Academic - admin and registrar
        if (in_array($role, ['admin', 'registrar'])) {
            $navigation[] = [
                'label' => 'Academic',
                'items' => [
                    ['title' => 'Curriculum', 'href' => '/curriculum', 'icon' => 'BookOpen'],
                    ['title' => 'Subjects', 'href' => '/subjects', 'icon' => 'BookText'],
                    ['title' => 'Students', 'href' => '/students', 'icon' => 'GraduationCap'],
                    ['title' => 'Teachers', 'href' => '/teachers', 'icon' => 'UserCheck'],
                    ['title' => 'Sections', 'href' => '/sections', 'icon' => 'LayoutList'],
                    ['title' => 'Enrollment', 'href' => '/enrollment', 'icon' => 'ClipboardList'],
                ],
            ];
        }

        // Grades - admin, registrar, and teacher
        if (in_array($role, ['admin', 'registrar', 'teacher'])) {
            $navigation[] = [
                'label' => 'Grades',
                'items' => [
                    ['title' => 'Grade Entry', 'href' => '/grades', 'icon' => 'FileSpreadsheet'],
                ],
            ];
        }

        // Reports - admin and registrar
        if (in_array($role, ['admin', 'registrar'])) {
            $navigation[] = [
                'label' => 'Reports',
                'items' => [
                    ['title' => 'Reports', 'href' => '/reports', 'icon' => 'BarChart3'],
                    ['title' => 'Import Data', 'href' => '/import', 'icon' => 'Upload'],
                ],
            ];
        }

        // My Records - student only
        if ($role === 'student') {
            $navigation[] = [
                'label' => 'My Records',
                'items' => [
                    ['title' => 'My Profile', 'href' => '/my/profile', 'icon' => 'User'],
                    ['title' => 'My Subjects', 'href' => '/my/subjects', 'icon' => 'BookOpen'],
                    ['title' => 'My Grades', 'href' => '/my/grades', 'icon' => 'FileSpreadsheet'],
                ],
            ];
        }

        return $navigation;
    }
}
