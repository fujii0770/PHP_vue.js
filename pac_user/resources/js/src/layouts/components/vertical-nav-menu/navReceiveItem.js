const menu = [
    {
        url: '/received',
        name: '受信一覧',
        slug: 'received',
        icon: 'MailIcon',
        loginRequired: true,
        customIcon: 'received',
        isReceivedOnly: true
    },
    {
        url: '/completed',
        name: '完了一覧',
        slug: 'completed',
        icon: 'CheckSquareIcon',
        loginRequired: true,
        customIcon: 'completed',
        isReceivedOnly: true
    },
    {
        url: '/download',
        name: 'ダウンロード状況確認',
        slug: 'download',
        icon: 'DownloadIcon',
        loginRequired: true,
        customIcon: 'download',
        isReceivedOnly: true
    },
    {
        name: 'ログアウト',
        icon: 'LogOutIcon',
        loginRequired: true,
        isLogout: true,
    },
    {
        name: "出退勤管理",
        icon: "CreditCardIcon",
        customIcon: 'timesheet',
        loginRequired: true,
        hr_flgRequired: true,
        slug: 'work',
        submenu:[
            {
                name: "タイムカード",
                url:"/hr/time_card",
                slug: 'time_card',
                loginRequired: true,
                hr_flgRequired: true
            },
            {
                url: '/hr/daily_report',
                name: "日報",
                slug: 'daily_report',
                loginRequired: true,
                hr_flgRequired: true
            },
            {
                url: '/hr/work_list',
                name: "勤務一覧",
                slug: 'work_list',
                loginRequired: true,
                hr_flgRequired: true
            },
            {
                url: '/hr/mail_setting',
                name: "設定",
                slug: 'mail_setting',
                loginRequired: true,
                hr_flgRequired: true
            },
            {
                url: '/hr/user_work_list',
                name: "勤務表一覧",
                slug: 'user_work_list',
                loginRequired: true,
                hr_flgRequired: true,
                hr_admin_flgRequired: true,
            },
            //route to go to work-detail screen
            // {
            //     name: "勤務詳細",
            //     url:"/hr/work-detail/:param",
            //     slug: 'work_detail',
            //     loginRequired: true,
            //     hr_flgRequired: true
            // },
        ]
    },
];
export default menu;
