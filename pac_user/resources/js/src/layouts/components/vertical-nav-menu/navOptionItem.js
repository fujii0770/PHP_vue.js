const menu = [
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
        url: '/download',
        name: 'ダウンロード状況確認',
        slug: 'download',
        icon: 'DownloadIcon',
        loginRequired: true,
        customIcon: 'download',
        isReceivedOnly: true
    },
];
export default menu;
