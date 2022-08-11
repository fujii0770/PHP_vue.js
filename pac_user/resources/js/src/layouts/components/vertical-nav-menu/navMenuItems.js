var menu = [
    {
        url: '/portal',
        name: 'マイページ',
        slug: 'portal',
        icon: 'PortalIcon',
        loginRequired: true,
        customIcon: 'portal-icon',
        isReceivedOnly: false
    },
    {
        url: '/',
        name: '新規作成',
        slug: 'creation',
        icon: 'HomeIcon',
        loginRequired: true,
        customIcon: 'creation',
        isReceivedOnly: false
    },
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
        url: '/sent',
        name: '送信一覧',
        slug: 'sent',
        icon: 'SendIcon',
        loginRequired: true,
        customIcon: 'sent',
        isReceivedOnly: false
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
        url: '/viewing',
        name: '閲覧一覧',
        slug: 'viewing',
        icon: 'ViewingIcon',
        loginRequired: true,
        customIcon: 'viewing',
        isReceivedOnly: true
    },
    {
        url: '/saved',
        name: '下書き一覧',
        slug: 'saved',
        icon: 'SaveIcon',
        loginRequired: true,
        customIcon: 'saved',
        isReceivedOnly: false
    },
    {
        url: '/document-search',
        name: '長期保管',
        slug: 'document',
        icon: 'SearchIcon',
        loginRequired: true,
        customIcon: 'document-search',
        isReceivedOnly: false
    },
    {
        name: 'アドレス帳',
        slug: 'Book',
        icon: 'BookOpenIcon',
        loginRequired: true,
        changeState: 'showModalContacts',
        isReceivedOnly: false
    },
    {
        url: '/template',
        name: 'テンプレート',
        slug: 'template',
        icon: 'TemplateIcon',
        loginRequired: true,
        customIcon: 'template',
        isReceivedOnly: false
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
        url: '/templatecsv',
        name: '回覧完了テンプレート一覧',
        slug: 'templatecsv',
        icon: 'TemplatecsvIcon',
        loginRequired: true,
        customIcon: 'templatecsv',
        isReceivedOnly: false
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
                name: "タイムカード（シフト）",
                url:"/hr/time_card_shift",
                slug: 'time_card_shift',
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
            {
                url: '/hr/user_work_status_list',
                name: "勤務状況確認",
                slug: 'user_work_status_list',
                loginRequired: true,
                hr_flgRequired: true,
                hr_admin_flgRequired: true,
            },
            {
                url: '/hr/daily_report_list',
                name: "日報一覧",
                slug: 'daily_report_list',
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
    {
        url: '/bizcard',
        name: '名刺管理',
        slug: 'bizcard',
        icon: 'CreditCardIcon',
        loginRequired: true,
        customIcon: 'bizcard'
    },
    {
        name: "ササッと明細",
        icon: "CreditCardIcon",
        customIcon: 'form-issuance',
        loginRequired: true,
        frm_srv_flgRequired: true,
        slug: 'form-template',
        submenu:[
            {
                name: "明細テンプレート一覧",
                url:"/form-issuance",
                slug: 'form-template',
                loginRequired: true,
                frm_srv_flgRequired: true
            },
            {
                url: '/form-issuance/form-list',
                name: "明細一覧",
                slug: 'form-list',
                loginRequired: true,
                frm_srv_flgRequired: true
            },
            {
                url: '/form-issuance/exp-template-list',
                name: "明細Expテンプレート",
                slug: 'form_exp_template',
                loginRequired: true,
                frm_srv_flgRequired: true
            },
        ]
    },
    {
        name: "経費精算",
        icon: "CreditCardIcon",
        // Todo update Iocon
        // customIcon: 'calculation-expense',
        customIcon: 'form-issuance',
        loginRequired: true,
        expense_flgRequired: true,
        slug: 'calculation-expense',
        submenu:[
            {
                name: "精算申請一覧",
                url:"/calculation-expense",
                slug: 'actuarial-application',
                loginRequired: true,
                expense_flgRequired: true,
            },
            {
                url: '/expense/received',
                name: '事前申請/精算申請一覧',
                slug: 'expense_received_list',
                icon: 'MailIcon',
                loginRequired: true,
                isReceivedOnly: true,
                expense_flgRequired: true,
            },
        ]
    },
    // {
    //     url: '/settings',
    //     name: '設定',
    //     slug: 'settings',
    //     icon: 'SettingsIcon',
    //     loginRequired: true
    // },
    {
        name: 'ログアウト',
        icon: 'LogOutIcon',
        loginRequired: true,
        isLogout: true,
    }
    // {
    //     url: "/tutorials",
    //     name: "チュートリアル",
    //     slug: "tutorials",
    //     icon: "VideoIcon",
    //     loginRequired: false
    // },
    // {
    //     url: "/helps",
    //     name: "ヘルプ",
    //     slug: "helps",
    //     icon: "HelpCircleIcon",
    //     loginRequired: false
    // },
]

var limit = getLS('limit')
limit = JSON.parse(limit)
if (limit && limit.enable_any_address == 1) {
    menu.splice(8, 1)
}
export default menu
