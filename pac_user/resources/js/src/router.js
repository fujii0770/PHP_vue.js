import Vue from 'vue'
import Router from 'vue-router'
import store from './store/store'

Vue.use(Router)

const originalPush = Router.prototype.push

Router.prototype.push = function push(location) {
  return originalPush.call(this, location).catch(err => err)
}

const router = new Router({
    mode: 'history',
    base: "app",
    scrollBehavior () {
        return { x: 0, y: 0 }
    },
    routes: [

        {
    // =============================================================================
    // MAIN LAYOUT ROUTES
    // =============================================================================
            path: '',
            component: resolve=>(require(['./layouts/main/Main.vue'],resolve)),
            children: [
        // =============================================================================
        // Theme Routes
        // =============================================================================
              {
                path: '/',
                name: 'create_circular',
                component: resolve=>(require(['./views/home/Index.vue'],resolve)),
                meta: {
                  title: '新規作成',
                  requiresAuth: true,
                  clearState: true,

                }
              },
              {
                path: '/create',
                name: 'create_new',
                component: resolve=>(require(["./views/home/create_new/Index.vue"],resolve)),
                meta: {
                  title: '新規作成',
                  requiresAuth: true,
                  clearState: true,

                }
              },
              {
                path: '/create/:id',
                name: 'create_new_detail',
                component: resolve=>(require(["./views/home/create_new/Index.vue"],resolve)),
                meta: {
                  title: '新規作成',
                  requiresAuth: true,
                  clearState: true,
                }
              },
              {
                path: '/approval',
                name: 'approval',
                component: resolve=>(require(["./views/home/create_new/Approval.vue"],resolve)),
                meta: {
                  title: '宛先、回覧順',
                  requiresAuth: true,
                  clearState: true,

                }
              },
              {
                path: '/saves/:id',
                name: 'save_detail',
                component: resolve=>(require(["./views/home/Index.vue"],resolve)),
                meta: {
                  title: '新規作成',
                  requiresAuth: true,
                  parent: 'saves',

                }
              },
              {
                path: '/received',
                name: 'received_list',
                component: resolve=>(require(["./views/received/Index.vue"],resolve)),
                meta: {
                  title: '受信一覧',
                  requiresAuth: true 
                }
              },
              {
                path: '/received/:id',
                name: 'received_detail',
                component: resolve=>(require(["./views/home/Index.vue"],resolve)),
                meta: {
                  title: '受信一覧:',
                  requiresAuth: true,
                  parent: 'received'
                }
              },
              {
                path: '/received-view/:id',
                name: 'received_view',
                component: resolve=>(require(["./views/received/View.vue"],resolve)),
                meta: {
                  title: '受信一覧:',
                  requiresAuth: true,
                  parent: 'received'
                }
              },
              {
                path: '/received-approval-sendback/:id',
                name: 'approval_sendback',
                component: resolve=>(require(["./views/received/View.vue"],resolve)),
                meta: {
                  title: '受信一覧:',
                  requiresAuth: true,
                  parent: 'received'
                }
              },
              {
                  path: '/received-reviewing/:id',
                  name: 'received-reviewing',
                  component: resolve=>(require(["./views/received/View.vue"],resolve)),
                  meta: {
                      title: '受信一覧:',
                      requiresAuth: true,
                      parent: 'received'
                  }
              },
              {
                    path: '/received-destination/:id',
                    name: 'received-destination',
                    component: resolve=>(require(["./views/received/destination.vue"],resolve)),
                    meta: {
                        title: '受信一覧:',
                        requiresAuth: true,
                        parent: 'received'
                    }
                },
              {
                path: '/sent',
                name: 'send_list',
                component: resolve=>(require(['./views/sent/Index.vue'],resolve)),
                meta: {
                  title: '送信一覧',
                  requiresAuth: true
                }
              },
              {
                path: '/sent/:id',
                name: 'sent_detail',
                component: resolve=>(require(['./views/sent/View.vue'],resolve)),
                meta: {
                  title: '送信一覧',
                  requiresAuth: true,
                  parent: 'sent'
                }
              },
              {
                path: '/completed',
                name: 'completed_list',
                component: resolve=>(require(['./views/completed/Index.vue'],resolve)),
                meta: {
                  title: '完了一覧',
                  requiresAuth: true
                }
              },
              {
                path: '/completed/:id',
                name: 'completed_detail',
                component: resolve=>(require(['./views/completed/View.vue'],resolve)),
                meta: {
                  title: '完了一覧',
                  requiresAuth: true,
                  parent: 'completed'
                }
              },
                {
                    path: '/viewing',
                    name: 'viewing_list',
                    component: resolve=>(require(['./views/viewing/Index.vue'],resolve)),
                    meta: {
                        title: '閲覧一覧',
                        requiresAuth: true
                    }
                },
                {
                    path: '/viewing/:id',
                    name: 'viewing',
                    component: resolve=>(require(['./views/viewing/View.vue'],resolve)),
                    meta: {
                        title: '閲覧一覧',
                        requiresAuth: true,
                        parent: 'viewing'
                    }
                },
                {
                    path: '/viewing/:id/memo',
                    name: 'memo',
                    component: resolve=>(require(['./views/viewing/Memo.vue'],resolve)),
                    meta: {
                        title: '閲覧一覧',
                        requiresAuth: true
                    }
                },
              {
                path: '/saved',
                name: 'save_list',
                component: resolve=>(require(['./views/saved/Index.vue'],resolve)),
                meta: {
                  title: '下書き一覧',
                  requiresAuth: true
                }
              },
              {
                path: '/document-search',
                name: 'document_list',
                component: resolve=>(require(['./views/document/Index.vue'],resolve)),
                meta: {
                  title: '長期保管',
                  requiresAuth: true
                }
              },
              {
                path: '/application',
                name: 'application',
                component: resolve=>(require(['./views/application/Index.vue'],resolve)),
                meta: {
                  title: '文書申請',
                  requiresAuth: true
              }
              },
              {
                path: '/download',
                name: 'download',
                component: resolve=>(require(['./views/download/Index.vue'],resolve)),
                meta: {
                  title: 'ダウンロード状況確認',
                  requiresAuth: true
                }
              },
              {
                path: '/settings',
                name: 'settings',
                component: resolve=>(require(['./views/settings/Index.vue'],resolve)),
                meta: {
                  title: '設定',
                  requiresAuth: true
                }
              },
              {
                path: '/destination',
                name: 'destination',
                component: resolve=>(require(['./views/circular_destination/Index.vue'],resolve)),
                meta: {
                  title: '回覧中',
                  requiresAuth: true
                }
              },
              {
                path: '/sendback',
                name: 'sendback',
                component: resolve=>(require(['./views/sendback/Index.vue'],resolve)),
                meta: {
                  title: '回覧中',
                  requiresAuth: true
                }
              },
              {
                path: 'hr/work_list',
                name: 'work_list',
                component: resolve=>(require(['./views/hr/work_list/Index.vue'],resolve)),
                meta: {
                  title: '勤務一覧',
                  requiresAuth: true,
                  requiresHrFlg: true,
                }
              },
                {
                    path: 'hr/daily_report',
                    name: 'daily_report',
                    component: resolve=>(require(['./views/hr/daily_report/Index.vue'],resolve)),
                    meta: {
                        title: '日報',
                        requiresAuth: true,
                        requiresHrFlg: true,
                    }
                },
                {
                    path: '/hr/time_card',
                    name: 'time-card',
                    component: resolve=>(require(['./views/hr/time_card/Index.vue'],resolve)),
                    meta: {
                        title: 'タイムカード',
                        requiresAuth: true,
                        requiresHrFlg: true,
                    }
                },
                {
                    path: '/hr/time_card_shift',
                    name: 'time-card-shift',
                    component: resolve=>(require(['./views/hr/time_card/Shift.vue'],resolve)),
                    meta: {
                        title: 'タイムカード(シフト)',
                        requiresAuth: true,
                        requiresHrFlg: true,
                    }
                },
                {
                  path: '/hr/user_work_list',
                  name: 'user_work_list',
                  component: resolve=>(require(['./views/hr/user_work_list/Index.vue'],resolve)),
                  meta: {
                    title: '勤務表一覧',
                    requiresAuth: true,
                    requiresHrFlg: true,
                    requiresHrAdminFlg: true,
                  }
                },
                {
                  path: '/hr/mail_setting',
                  name: 'hr_mail_setting',
                  component: () => import('./views/hr/mail_setting/Index.vue'),
                  meta: {
                    title: '設定',
                    requiresAuth: true,
                    requiresHrFlg: true,
                  }
                },
                {
                  path: '/hr/user_work_detail/:id/:working_month',
                  name: 'user_work_detail',
                  component: resolve=>(require(['./views/hr/user_work_detail/Index.vue'],resolve)),
                  meta: {
                    title: 'ユーザー勤務詳細',
                    requiresAuth: true,
                    requiresHrFlg: true,
                    requiresHrAdminFlg: true,
                  }
                },
                {
                  path: '/hr/user_work_status_list',
                  name: 'user_work_status_list',
                  component: resolve=>(require(['./views/hr/user_work_status_list/Index.vue'],resolve)),
                  meta: {
                    title: '勤務状況確認',
                    requiresAuth: true,
                    requiresHrFlg: true,
                    requiresHrAdminFlg: true,
                  }
                },
                //add new path to Work Detail Screen
                {
                    path: '/hr/work-detail/:param',
                    name: 'work_detail',
                    component: resolve=>(require(['./views/hr/work-detail/Index.vue'],resolve)),
                    meta: {
                      title: '勤務詳細',
                      requiresAuth: true,
                      requiresHrFlg: true,
                    }
                },
                {
                    path: '/hr/daily_report_list',
                    name: 'daily_report_list',
                    component: resolve=>(require(['./views/hr/daily_report/AdminList.vue'],resolve)),
                    meta: {
                        title: '日報一覧',
                        requiresAuth: true,
                        requiresHrFlg: true,
                        requiresHrAdminFlg: true,
                    }
                },
                {
                    path: '/hr/daily_report_list/:id',
                    name: 'daily_report_edit',
                    component: resolve=>(require(['./views/hr/daily_report/View.vue'],resolve)),
                    meta: {
                        title: '日報',
                        requiresAuth: true,
                        requiresHrFlg: true,
                        requiresHrAdminFlg: true,
                    }
                },
              {
                path: '/bizcard',
                name: 'bizcard',
                component: resolve=>(require(['./views/bizcard/Index.vue'],resolve)),
                meta: {
                  title: '名刺管理',
                  requiresAuth: true
                }
              },
              {
                path: '/template',
                name: 'template_list',
                component: resolve=>(require(['./views/template/Index.vue'],resolve)),
                meta: {
                  title: 'テンプレート保存一覧',
                  requiresAuth: true
                }
              },
              {
                path: '/template/update',
                name: 'template_update_no_id',
                component: resolve=>(require(['./views/template/Update.vue'],resolve)),
                meta: {
                  title: 'テンプレート入力',
                  requiresAuth: true,
                  parent: 'template_list'
                }
              },
              {
                path: '/template/update/:id',
                name: 'template_update',
                component: resolve=>(require(['./views/template/Update.vue'],resolve)),
                meta: {
                  title: 'テンプレート入力',
                  requiresAuth: true,
                  parent: 'template_list'
                }
              },
              {
                path: '/portal',
                name: 'portal',
                component: resolve=>(require(['./views/portal/Index.vue'],resolve)),
                meta: {
                  title: 'マイページ',
                  requiresAuth: true
                }
              },
              {
                path: '/groupware/calendar',
                name: 'groupware_calendar',
                component: resolve=>(require(['./views/portal/groupware/Calendar.vue'],resolve)),
                meta: {
                  title: 'スケジューラ',
                  requiresAuth: true
                }
              },
              {
                path: '/groupware/faq_bulletin',
                name: 'groupware_faq_bulletin',
                component: resolve=>(require(['./views/portal/groupware/FaqBulletinBoard.vue'],resolve)),
                meta: {
                  title: 'サポート掲示板',
                  requiresAuth: true
                }
              },
              {
                path: '/groupware/bulletin',
                name: 'groupware_bulletin',
                component: resolve=>(require(['./views/portal/groupware/BulletinBoard.vue'],resolve)),
                meta: {
                  title: '掲示板',
                  requiresAuth: true
                }
              },
                {
                    path: '/groupware/time-card',
                    name: 'groupware_timecard',
                    component: resolve=>(require(['./views/portal/groupware/TimeCardBig.vue'],resolve)),
                    meta: {
                        title: 'タイムカード',
                        requiresAuth: true
                    }
                },
                {
                    path: '/groupware/time-card/detail',
                    name: 'groupware_timecard_detail',
                    component: resolve=>(require(['./views/portal/groupware/TimeCardDetail.vue'],resolve)),
                    meta: {
                        title: '打刻一覧',
                        requiresAuth: true
                    }
                },
                {
                    path: '/groupware/file_mail/application',
                    name: 'file_mail_application',
                    component: resolve=>(require(['./views/portal/groupware/file_mail/Application.vue'],resolve)),
                    meta: {
                        title: 'ファイルメール便',
                        requiresAuth: true
                    }
                },
                {
                    path: '/groupware/file_mail/list',
                    name: 'file_mail_list',
                    component: resolve=>(require(['./views/portal/groupware/file_mail/List.vue'],resolve)),
                    meta: {
                        title: 'ファイルメール便',
                        requiresAuth: true
                    }
                },
                {
                    path: '/groupware/file_mail/confirm',
                    name: 'file_mail_confirm',
                    component: resolve=>(require(['./views/portal/groupware/file_mail/Confirm.vue'],resolve)),
                    meta: {
                        title: 'ファイルメール便',
                        requiresAuth: true
                    }
                },
              {
                path: '/groupware/Personal',
                name: 'groupware_personal',
                component: resolve=>(require(['./views/portal/groupware/Personal.vue'],resolve)),
                meta: {
                  title: '個人設定',
                  requiresAuth: true
                }
              },
                {
                    path: '/personal/adrress_list',
                    name: 'adrress_list',
                    component: () => import('./views/portal/groupware/adrressList'),
                    meta: {
                        title: '利用者名簿',
                        requiresAuth: true
                    }
                },
              {
                path: '/groupware/Notification',
                name: 'groupware_notification',
                component: resolve=>(require(['./views/portal/groupware/Notification.vue'],resolve)),
                meta: {
                  title: '通知設定',
                  requiresAuth: true
                }
              },
              {
                path: '/groupware/MyGroup',
                name: 'groupware_myGroup',
                component: resolve=>(require(['./views/portal/groupware/MyGroup.vue'],resolve)),
                meta: {
                  title: 'マイグループ設定',
                  requiresAuth: true
                }
              },
              {
                path: '/groupware/CaldavSetting',
                name: 'groupware_caldavSetting',
                component: resolve=>(require(['./views/portal/groupware/CaldavSetting.vue'],resolve)),
                meta: {
                  title: 'カレンダー連携設定',
                  requiresAuth: true
                }
              },
                {
                    path: '/groupware/to-do-list',
                    name: 'groupware_to_do_list',
                    component: resolve=>(require(['./views/portal/groupware/ToDoList.vue'],resolve)),
                    meta: {
                        title: 'Todoリスト',
                        requiresAuth: true
                    }
                },
              {
                path: '/templatecsv',
                name: 'template_csvoutput',
                component: resolve=>(require(['./views/template_csv/Index.vue'],resolve)),
                meta: {
                  title: '回覧完了テンプレート一覧',
                  requiresAuth: true
                }
              },
              {
                path: '/page-breaks',
                name: 'page_breaks',
                component: resolve=>(require(['./views/page_breaks/Index.vue'],resolve)),
                meta: {
                  title: '改ページ調整',
                  requiresAuth: true,
                }
              },
              {
                path: '/form-issuance',
                name: 'form_issuance_list',
                component: resolve=>(require(['./views/form_issuance/Index.vue'],resolve)),
                meta: {
                  title: '明細テンプレート一覧',
                  requiresAuth: true,
                  requiresFrmSrvFlg: true,
                }
              },
              {
                path: '/form-issuance/create',
                name: 'form_issuance_create',
                component: resolve=>(require(['./views/form_issuance/Create.vue'],resolve)),
                meta: {
                  title: '明細の作成',
                  requiresAuth: true,
                  requiresFrmSrvFlg: true,
                  parent: 'form_issuance_list'
                }
              },
              {
                path: '/form-issuance/import',
                name: 'form_issuance_import',
                component: resolve=>(require(['./views/form_issuance/ImportCsv.vue'],resolve)),
                meta: {
                  title: '明細インポート',
                  requiresAuth: true,
                  requiresFrmSrvFlg: true                }
              },
              {
                path: '/form-issuance/form-list',
                name: 'form-issuance',
                component: resolve=>(require(['./views/form_issuance/FormList.vue'],resolve)),
                meta: {
                  title: '明細一覧',
                  requiresAuth: true,
                  requiresFrmSrvFlg: true,
                }
              },
              {
                path: '/form-issuance/exp-template-list',
                name: 'form_exp_template',
                component: resolve=>(require(['./views/form_issuance/ExpTemplate.vue'],resolve)),
                meta: {
                  title: '明細Expテンプレート一覧',
                  requiresAuth: true,
                  requiresFrmSrvFlg: true,
                }
              },
              {
                path: '/form-issuance/:id/:other/:issu_id',
                name: 'form-issuance_detail',
                component: resolve=>(require(['./views/form_issuance/View.vue'],resolve)),
                meta: {
                  title: '明細詳細',
                  requiresAuth: true,
                  requiresFrmSrvFlg: true,
                  parent: 'form-issuance'
                }
              },
              {
                path: '/form-issuance/setting/:id',
                name: 'form-issuance_setting',
                component: resolve=>(require(['./views/form_issuance/Setting'],resolve)),
                meta: {
                  title: '明細テンプレート設定',
                  requiresAuth: true,
                  requiresFrmSrvFlg: true,
                }
              },

              // calculation-expense
              {
                  path: '/calculation-expense',
                  name: 'actuarial-application',
                  component: () => import('./views/calculation_expense/Index.vue'),
                  meta: {
                      title: '申請書／精算書一覧',
                      requiresAuth: true,
                  }
              },
              {
                  path: '/calculation-expense/expense-form',
                  name: 'expense-form',
                  component: () => import('./views/calculation_expense/ExpenseForm.vue'),
                  meta: {
                      title: '経費申請書作成',
                      requiresAuth: true,
                  }
              },
                {
                    path: '/calculation-expense/expense-form/:id',
                    name: 'expense-form-detail',
                    component: () => import('./views/calculation_expense/ExpenseForm.vue'),
                    meta: {
                        title: '経費申請書詳細',
                        requiresAuth: true,
                    }
                },

                {
                    path: '/calculation-expense/rebate-form/:id',
                    name: 'rebate-form-detail',
                    component: () => import('./views/calculation_expense/ExpenseForm.vue'),
                    meta: {
                        title: '経費精算申請書詳細',
                        requiresAuth: true,
                    }
                },
                {
                    path: '/calculation-expense/rebate-form',
                    name: 'rebate-form',
                    component: () => import('./views/calculation_expense/ExpenseForm.vue'),
                    meta: {
                        title: '経費精算書作成',
                        requiresAuth: true,
                    }
                },
                {
                    path: '/expenses/:id',
                    name: 'expense_detail',
                    component: resolve=>(require(["./views/home/Index.vue"],resolve)),
                    meta: {
                        title: '新規作成',
                        requiresAuth: true,
                    }
                },

                {
                    path: '/special/template/update/:flg',
                    name: 'special_site_template_update',
                    component: resolve=>(require(['./views/template/Update.vue'],resolve)),
                    meta: {
                        title: 'テンプレート入力',
                        requiresAuth: true,
                        parent: 'template_list'
                    }
                },
              // PAC_5-2352 START
              {
                path: '/skipCurrentAction',
                name: 'received_skipCurrentAction',
                component: resolve=>(require(['./views/sent/skipCurrentAction.vue'],resolve)),
                meta: {
                    title: 'スキップ',
                        requiresAuth: true,
                }
              },
              // PAC_5-2352 END
              /*PAC_5-3018 S*/
              {
                  path: '/personal/setting',
                  name: 'personal_setting',
                  component: resolve=>(require(['./views/portal/groupware/PersonalSetting'],resolve)),
                  meta: {
                      title: '個人設定',
                      requiresAuth: true
                  }
              },
              /*PAC_5-3018 E*/
              {
                path: '/groupware/receive_plan',
                name: 'groupware_receive_plan',
                component: resolve=>(require(['./views/portal/groupware/ReceivePlan.vue'],resolve)),
                meta: {
                    title: '受信専用プラン',
                    requiresAuth: true,
                }
              },
              {
                path: '/expense/received',
                name: 'expense_received_list',
                component: () => import('./views/expense_received/Index.vue'),
                meta: {
                  title: '事前申請/精算申請一覧',
                  requiresAuth: true
                }
              },
              {
                path: '/expense/received/:id',
                name: 'expense_received_detail',
                component: () => import('./views/home/Index.vue'),
                meta: {
                  title: '事前申請/精算申請一覧',
                  requiresAuth: true,
                  parent: 'expense_received_list'
                }
              },
              {
                path: '/expense/received-view/:id',
                name: 'expense_received_view',
                component: () => import('./views/received/View.vue'),
                meta: {
                  title: '事前申請/精算申請一覧',
                  requiresAuth: true,
                  parent: 'expense_received_list'
                }
              },
              {
                path: '/expense/received-approval-sendback/:id',
                name: 'expense_approval_sendback',
                component: () => import('./views/received/View.vue'),
                meta: {
                  title: '事前申請/精算申請一覧',
                  requiresAuth: true,
                  parent: 'expense_received_list'
                }
              },
              {
                path: '/expense/sendback',
                name: 'expense_sendback',
                component: () => import('./views/sendback/Index.vue'),
                meta: {
                  title: '事前申請/精算申請一覧',
                  requiresAuth: true,
                  parent: 'expense_received_list'
                }
              },
              {
                  path: '/expense/received-reviewing/:id',
                  name: 'expense_received-reviewing',
                  component: () => import('./views/received/View.vue'),
                  meta: {
                      title: '事前申請/精算申請一覧',
                      requiresAuth: true,
                      parent: 'expense_received_list'
                  }
              },
              {
                   path: '/expense/destination',
                   name: 'expense_destination',
                   component: () => import('./views/circular_destination/Index.vue'),
                   meta: {
                       title: '事前申請/精算申請一覧',
                       requiresAuth: true,
                       parent: 'expense_received_list'
                   }
                },
            ],
        },
        {
          path: '',
          component: resolve=>(require(['./layouts/main/PublicMain.vue'],resolve)),
          children: [
            {
              path: 'site/showCardView/:token',
              name: 'public_showCard',
              component: resolve=>(require(['./views/public/ShowCardView.vue'],resolve)),
              meta: {
                title: '名刺情報表示',
              }
            },
            {
              path: 'site/approval/:hash',
              name: 'public_approval',
              component: resolve=>(require(['./views/public/Approval.vue'],resolve)),
              meta: {
                title: '回覧中',
              }
            },
            {
              path: 'site/destination/:hash',
              name: 'public_destination',
              component: resolve=>(require(['./views/circular_destination/Index.vue'],resolve)),
              meta: {
                title: '回覧中',
              }
            },
            {
              path: 'site/sendback/:hash',
              name: 'public_sendback',
              component: resolve=>(require(['./views/sendback/Index.vue'],resolve)),
              meta: {
                title: '回覧中',
              }
            },
            {
              path: 'site/memo/:hash',
              name: 'public_memo',
              component: resolve=>(require(['./views/public/Memo.vue'],resolve)),
              meta: {
                 title: '回覧中',
              }
            },
              {
                  path: 'site/receive/:hash',
                  name: 'public_received',
                  component: resolve=>(require(['./views/public/destination.vue'],resolve)),
                  meta: {
                      title: '回覧中',
                  }
              },
            // PAC_5-2242 Start
            {
              path: 'site/page-breaks/:hash',
              name: 'public_page_breaks',
              component: resolve=>(require(['./views/page_breaks/Index.vue'],resolve)),
              meta: {
                title: '改ページ調整',
              }
            },

            // PAC_5-2242 End
          ]
        },
    // =============================================================================
    // FULL PAGE LAYOUTS
    // =============================================================================
        {
            path: '',
            component: resolve=>(require(['@/layouts/full-page/FullPage.vue'],resolve)),
            children: [
        // =============================================================================
        // PAGES
        // =============================================================================
              {
                path: '/pages/login',
                name: 'page-login',
                component: resolve=>(require(['@/views/pages/Login.vue'],resolve)),
              },
              {
                path: '/pages/error-404',
                name: 'page-error-404',
                component: resolve=>(require(['@/views/pages/Error404.vue'],resolve)),
              },
              {
                path: '/StampInfo/:info_id',
                name: 'StampInfo',
                component: resolve=>(require(['@/views/pages/StampInfo.vue'],resolve)),
                meta: {
                  title: '捺印プロパティ'
                }
              },
            ]
        },
        //経費精算
        {
          path: '/expense/preview',
          name: 'expense_preview_no_id',
          component: () => import('./views/expense_circular/Index.vue'),
        },
        {
            path: '/expense/preview/:id',
            name: 'expense_preview',
            component: () => import('./views/expense_circular/Index.vue'),
        },

        // Redirect to 404 page, if no match found
        {
            path: '*',
            redirect: '/pages/error-404'
        }
    ],
});

router.beforeEach(async (to, from, next) => {
  document.title = 'Shachihata Cloud ' + '- ' + to.meta.title;
  document.body.style.overflow = '';


  if( (1 == store.state.setting.withdrawal_caution) && (true == store.state.home.closeCheck) ) {
    let cfirm_flg = false;
      if( ('新規作成' == from.meta.title) && (0 != store.state.home.files.length) && ('文書申請' != to.meta.title) ) {
        cfirm_flg = true;
    } else if( ('改ページ調整' == from.meta.title) || ('改ページ調整' == to.meta.title) ) {
      cfirm_flg = true;
    }
    if( true == cfirm_flg ) {
      let result = window.confirm('行った変更が保存されない可能性があります。');
      if( false == result ) {
        next(false);
        return;
      }
    }
  }
  store.commit('home/setCloseCheck', true );

  // 改ページ調整に関係ない画面(新規作成、下書き、受信、改ページ調整以外の画面)に遷移する場合は、
  // 改ページ調整に必要な情報を初期化して削除する
  // PAC_5-2242 メールページを追加 public_approval,public_page_breaks
  if (!['create_circular', 'save_detail', 'received_detail', 'page_breaks', 'public_approval', 'public_page_breaks'].includes(to.name)) {
    store.dispatch('pageBreaks/initCircularIdAndRegisteredDocInfoList', null, {root : true});
  }

  if (to.matched.some(record => record.meta.requiresAuth)) {
    var user = getLS('user');
    user = JSON.parse(user);
    if (user && user.id) {
        if (to.meta.requiresHrFlg) {
            if (!user.hr_user_flg) {
                window.location.href = '/app/pages/error-404';
            }
        }
        if (to.meta.requiresHrAdminFlg) {
            if (!user.hr_admin_flg) {
                window.location.href = '/app/pages/error-404';
            }
        }
        if (to.meta.requiresFrmSrvFlg) {
            if (!user.frm_srv_user_flg) {
                window.location.href = '/app/pages/error-404';
            }
        }
      next();
      return;
    }

    localStorage.removeItem('token');
    localStorage.removeItem('user');
    localStorage.removeItem('expires_time');
    const return_url = localStorage.getItem('return_url');
    window.location.href = return_url;

    this.$ls.set(`logout`, Date.now());
  } else {
    next(); // make sure to always call next()!
  }
});

router.afterEach(() => {
  var user = getLS('user');
  user = JSON.parse(user);
  if (user && user.id) {
    // if(window.location.pathname == "/app/portal" && navigator.userAgent.match(/(phone|iPhone|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)){
    //   window.location.href = "/app/received"
    // }
    // if(window.location.pathname == "/app/sent" && navigator.userAgent.match(/(phone|iPhone|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)){
    //   window.location.href = "/app/received"
    // }
    // if(window.location.pathname == "/app/saved" && navigator.userAgent.match(/(phone|iPhone|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)){
    //   window.location.href = "/app/received"
    // }
    if(( window.location.pathname == "/app/" || window.location.pathname == "/app" ) && navigator.userAgent.match(/(phone|iPhone|iPad|Android|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)){
      window.location.href = "/app/create"
    }
  }
  //pac_5-2224 モバイル初期ページをポータルから受信一覧へ"/app"→"/app/portal"

  // Remove initial loading
  const appLoading = document.getElementById('loading-bg')
    if (appLoading) {
        appLoading.style.display = "none";
    }
});


export default router
