const menu = [
    {
        url: '/received',
        name: '受信一覧',
        slug: 'received',
        icon: 'MailIcon',
        loginRequired: true
    },
    {
        url: '/completed',
        name: '完了一覧',
        slug: 'completed',
        icon: 'CheckSquareIcon',
        loginRequired: true
    },
    {
        url: '/document-search',
        name: '長期保管',
        slug: 'document',
        icon: 'SearchIcon',
        loginRequired: true
    },
    {
        name: 'アドレス帳',
        icon: 'BookOpenIcon',
        loginRequired: true,
        changeState: 'showModalContacts'
    },
    {
        url: '/bizcard',
        name: '名刺管理',
        slug: 'bizcard',
        icon: 'CreditCardIcon',
        loginRequired: true,
    },
    {
        url: '/download',
        name: 'ダウンロード状況確認',
        slug: 'download',
        icon: 'DownloadIcon',
        loginRequired: true,
    },
    {
        url: '/settings',
        name: '設定',
        slug: 'settings',
        icon: 'SettingsIcon',
        loginRequired: true
    },
    {
        url: '/portal',
        name: 'マイページ2',
        slug: 'portal',
        icon: 'PortalIcon',
        loginRequired: true,
        customIcon: 'portal-icon'
    },

];
export default menu
