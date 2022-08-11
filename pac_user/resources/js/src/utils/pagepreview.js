// ページ画像取得関連

// 取得開始してもよい画像であるか を返す
const isPageImageNeeded = (item) => { // pages / thumbnails item
  if (!item) {
    // out of range
    return false;
  }
  const loaded = !!item.imageUrl;
  return !(loaded || item.loading || item.failed);
};

const getImageSize = (url) => {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve({width: img.width, height:img.height});
    img.onerror = reject
    img.src = url;
  })
};

const getRequiredImagesUsingWorkers = async (getNextRequestImage, getPageImage) => {
  const WORKER_NUM = 4;
  let loop = true;
  let abortedByError = false;

  const worker = async () => {
    while (loop) {
      const req = getNextRequestImage();
      if (!req) {
        // 全worker 止めさせる
        loop = false;
      } else {
        const result = await getPageImage(req.no, req.isThumbnail);

        const isError = !result.fileChanged && !result.ok;
        abortedByError = abortedByError || isError;
        if (isError) {
          // 全worker 止めさせる
          loop = false;
        }
      }
    }
  };

  const workers = [];
  const generateWorkers = () => {
    while (getNextRequestImage() && workers.length < WORKER_NUM) {
      workers.push(worker());
    }
    const filled = workers.length >= WORKER_NUM;
    return filled;
  }

  // loop == true の間 worker数上限に達するまで
  // 一定時間ごとにチェックする
  await new Promise(resolve => {
    let timer;

    const stop = () => {
      if (timer) {
        clearInterval(timer);
      }
      resolve();
    };

    const checker = () => {
      if (loop) {
        const filled = generateWorkers();
        if (filled) {
          stop();
        }
      } else {
        stop();
      }
    };

    timer = setInterval(checker, 50);
    checker();
  });

  // 1つだけ待つ
  // 1つ終了したら他も loop == false のため処理打ち切るはず
  await Promise.race(workers);

  return abortedByError;
};

const getPageUtil = {
  // computed
  hasRequestFailedImage(pages, thumbnails) {
    // 失敗した画像取得があれば true を返す
    const failedItem = pages.find(item => item.failed) ?? thumbnails.find(item => item.failed);
    return !!failedItem;
  },
  nextRequestImage(pages, thumbnails, hasRequestFailedImage, showLeftToolbar, visiblePageRange, visibleThumbnailRange) {
    // 次に取得するべきファイル情報を返す
    if (hasRequestFailedImage) {
      return null;
    }

    const isNeeded = isPageImageNeeded;

    const checkRange = (start, end, array) => {
      for (let i = start; i < end; i++) {
        if (isNeeded(array[i])) {
          return [array, i];
        }
      }
      return null;
    };

    const checkNearby = (start, end, nearbyCount, array) => {
      for (let i = 1; i <= nearbyCount; i++) {
        const checkedIndexes = [
          // 後
          end + i - 1,
          // 前
          start - i,
        ];

        for (const index of checkedIndexes) {
          if (isNeeded(array[index])) {
            return [array, index];
          }
        }
      }
      return null;
    }

    const [pageStart, pageEnd] = visiblePageRange;
    const [thumbnailStart, thumbnailEnd] = visibleThumbnailRange;

    const isThumbnailHidden = !showLeftToolbar;
    const visiblePageExists = pageStart != -1;
    const visibleThumbnailExists = thumbnailStart != -1;

    // サムネイル表示中の場合、表示中ページ・サムネイル 両方の情報が揃ってから取得開始
    // サムネイルを先に取得するのを防止するため
    const isReady = visiblePageExists && (visibleThumbnailExists || isThumbnailHidden);
    if (!isReady) {
      return null;
    }

    const checkers = [
      // 見えているプレビューのサムネイル
      () => visibleThumbnailExists && checkRange(pageStart, pageEnd, thumbnails),
      // 見えているプレビュー
      () => checkRange(pageStart, pageEnd, pages),
      // 見えているサムネイル
      () => visibleThumbnailExists && checkRange(thumbnailStart, thumbnailEnd, thumbnails),
      // 見えているプレビュー 前後2ページ
      () => checkNearby(pageStart, pageEnd, 2, pages),
      // 見えているサムネイル 前後10ページ
      () => visibleThumbnailExists && checkNearby(thumbnailStart, thumbnailEnd, 10, thumbnails),
    ];

    for (const checker of checkers) {
      const nextInfo = checker();
      if (nextInfo) {
        const [array, index] = nextInfo;

        return {
          no: index + 1,
          isThumbnail: array === thumbnails,
        };
      }
    }

    return null;
  },
  mobilePages(pages) {
    // 1~取得済みページ
    const firstNotLoadedIndex = pages.findIndex(item => {
      const loaded = !!item.imageUrl;
      return !loaded;
    });
    const end = firstNotLoadedIndex == -1 ? pages.length : firstNotLoadedIndex;

    return pages.slice(0, end);
  },

  // その他
  async handleGetPageResult(storeTo, isThumbnail, getPagePromise) {
    storeTo.loading = true;

    const result = await getPagePromise.then(data => {
      return {
        ok: true,
        url: data.imageUrl,
      };
    }, e => {
      return {
        ok: false,
        fileChanged: e.fileChanged,
      };
    });

    storeTo.loading = false;

    if (!result.ok) {
      storeTo.failed = true;
      return {
        ok: false,
        fileChanged: result.fileChanged,
      };
    }

    if (isThumbnail) {
      const thumbnail = storeTo;
      thumbnail.imageUrl = result.url;
    } else {
      const page = storeTo;

      storeTo.loading = true; // 再取得防止用
      const imageSize = await getImageSize(result.url);
      storeTo.loading = false;

      page.editorParam.width = ~~((96/150) * +imageSize.width);
      page.editorParam.height = ~~((96/150) * +imageSize.height);
      page.imageUrl = result.url;
    }

    return {
      ok: true,
    };
  },
  getPageImagesForMobile(pages, getPageImage) {
    const promises = [];

    for (let i = 0; i < 8; i++) {
      const isNeeded = isPageImageNeeded(pages[i]);
      const promise = isNeeded ? getPageImage(i + 1, false) : null;

      promises.push(promise);
    }

    return promises;
  },
  clearImageErrors(pages, thumbnails) {
    for (const item of pages) {
      item.failed = false;
    }
    for (const item of thumbnails) {
      item.failed = false;
    }
  },
  createPages(pageCount, storesPages) {
    const pages = new Array(pageCount);
      let pagesTemp=Object.assign([],storesPages);
      pagesTemp.forEach((page,index)=>{
          page.stamps.forEach((stamp,key)=>{
              if (stamp.selected){
                  pagesTemp[index].stamps.splice(key,1)
              }
          })
      })
    for (let i = 0; i < pages.length; i++) {
      pages[i] = {
        editorParam: {
          ...storesPages[i],
          width: null,
          height: null,
        },
        imageUrl: null,
        loading: false,
        failed: false,
      };
    }

    return pages;
  },
  createThumbnails(pageCount) {
    const thumbnails = new Array(pageCount);
    for (let i = 0; i < thumbnails.length; i++) {
      thumbnails[i] = {
        no: i + 1,

        imageUrl: null,
        loading: false,
        failed: false,
      };
    }

    return thumbnails;
  },
  async getRequiredImages(getNextRequestImage, getPageImage) {
    let loop = true;

    while (loop) {
      const abortedByError = await getRequiredImagesUsingWorkers(getNextRequestImage, getPageImage);

      // 取得すべきものがなくなる もしくは エラーとなれば処理終了
      // もし取得すべきものがまだあればもう一度
      loop = !abortedByError && getNextRequestImage();
    }
  }
};

export {
  getPageUtil
};