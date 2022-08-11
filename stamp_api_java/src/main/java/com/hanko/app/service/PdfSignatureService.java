package com.hanko.app.service;

import java.awt.FontFormatException;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.security.GeneralSecurityException;
import java.security.KeyStore;
import java.security.PrivateKey;
import java.security.Security;
import java.security.cert.Certificate;
import java.security.cert.CertificateException;
import java.security.cert.CertificateFactory;
import java.security.cert.X509Certificate;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;
import java.util.logging.Level;
import java.util.logging.Logger;

import com.hanko.app.utils.ConvertColorValue;
import org.bouncycastle.cert.ocsp.OCSPException;
import org.bouncycastle.jce.provider.BouncyCastleProvider;
import org.bouncycastle.operator.OperatorCreationException;
import org.bouncycastle.x509.util.StreamParsingException;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.core.io.ClassPathResource;
import org.springframework.core.io.Resource;
import org.springframework.stereotype.Service;
import org.springframework.util.StringUtils;

import com.hanko.app.bean.StampRequest;
import com.hanko.app.bean.TextRequest;
import com.hanko.app.cert.utils.AdobeUtil;
import com.hanko.app.controller.PdfController;
import com.hanko.app.utils.FontUtils;
import com.hanko.app.utils.TrackExecutionTime;
import com.itextpdf.io.codec.Base64;
import com.itextpdf.io.font.PdfEncodings;
import com.itextpdf.io.image.ImageData;
import com.itextpdf.io.image.ImageDataFactory;
import com.itextpdf.io.source.ByteUtils;
import com.itextpdf.kernel.font.PdfFont;
import com.itextpdf.kernel.font.PdfFontFactory;
import com.itextpdf.kernel.geom.AffineTransform;
import com.itextpdf.kernel.geom.Rectangle;
import com.itextpdf.kernel.pdf.PdfArray;
import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfName;
import com.itextpdf.kernel.pdf.PdfNumber;
import com.itextpdf.kernel.pdf.PdfPage;
import com.itextpdf.kernel.pdf.PdfReader;
import com.itextpdf.kernel.pdf.PdfString;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.kernel.pdf.StampingProperties;
import com.itextpdf.kernel.pdf.action.PdfAction;
import com.itextpdf.kernel.pdf.annot.PdfAnnotation;
import com.itextpdf.kernel.pdf.annot.PdfLinkAnnotation;
import com.itextpdf.kernel.pdf.annot.PdfTextAnnotation;
import com.itextpdf.kernel.pdf.canvas.PdfCanvas;
import com.itextpdf.signatures.BouncyCastleDigest;
import com.itextpdf.signatures.CrlClientOnline;
import com.itextpdf.signatures.DigestAlgorithms;
import com.itextpdf.signatures.ICrlClient;
import com.itextpdf.signatures.IExternalDigest;
import com.itextpdf.signatures.IExternalSignature;
import com.itextpdf.signatures.ITSAClient;
import com.itextpdf.signatures.PacSigner;
import com.itextpdf.signatures.PdfSignature;
import com.itextpdf.signatures.PdfSigner;
import com.itextpdf.signatures.PrivateKeySignature;
import com.itextpdf.signatures.TSAClientBouncyCastle;

@Service
public class PdfSignatureService implements IPdfSignatureService {

	private Map<String, X509Certificate> mapCertificates;

	private X509Certificate timestampCertificate;

	private ICrlClient customCrlClient;

	@Value("${tas.url}")
	private String tasUrl;

	@Value("${tas.username}")
	private String tasUsername;

	@Value("${tas.password}")
	private String tasPassword;

	@Value("${signature.location}")
	private String location;

	public PdfSignatureService(@Value("${certificate.cn.names}") String[] certificateCnNames,
			@Value("${certificate.parent.files}") String[] certificateParents, @Value("${crl.urls}") String[] crlUrls,
			@Value("${crl.files}") String[] crlFiles,
			@Value("${certificate.timestamp.file}") String timestampCertificate) {

		this.mapCertificates = new HashMap<String, X509Certificate>();
		if (certificateCnNames != null) {
			try {
				CertificateFactory cf = CertificateFactory.getInstance("X509");

				for (int i = 0; i < certificateCnNames.length && i < certificateParents.length; i++) {
					Resource resource = new ClassPathResource(certificateParents[i]);

					mapCertificates.put("CN=" + certificateCnNames[i],
							(X509Certificate) cf.generateCertificate(resource.getInputStream()));
				}
			} catch (CertificateException | IOException ex) {
				Logger.getLogger(PdfController.class.getName()).log(Level.SEVERE, null, ex);
			}
		}

		Map<String, String> offlineUrls = new HashMap<String, String>();
		if (crlUrls != null && crlFiles != null) {
			for (int i = 0; i < crlUrls.length && i < crlFiles.length; i++) {
				offlineUrls.put(crlUrls[i], crlFiles[i]);
			}
		}
		try {
			CertificateFactory cf = CertificateFactory.getInstance("X509");

			Resource resource = new ClassPathResource(timestampCertificate);
			this.timestampCertificate = (X509Certificate) cf.generateCertificate(resource.getInputStream());
		} catch (CertificateException | IOException ex) {
			Logger.getLogger(PdfController.class.getName()).log(Level.SEVERE, null, ex);
		}

		// this.customCrlClient = new CustomCrlClient(offlineUrls);
		this.customCrlClient = new CrlClientOnline();
	}

	@Override
	@TrackExecutionTime
	public boolean impressPdf(ArrayList<TextRequest> texts, ArrayList<StampRequest> stamps, InputStream inputStream,
			OutputStream outputStream, InputStream appendedInputStream) {
		try {
			PdfWriter pdfWriter = new PdfWriter(outputStream);

			PdfDocument pdfDoc = new PdfDocument(new PdfReader(inputStream), pdfWriter,
					new StampingProperties().preserveEncryption().useAppendMode());

			Map<Integer, ArrayList<TextRequest>> mapTextPerPage = new HashMap<Integer, ArrayList<TextRequest>>();
			if (texts != null) {
				for (TextRequest text : texts) {
					if (mapTextPerPage.containsKey(text.getPage())) {
						mapTextPerPage.get(text.getPage()).add(text);
					} else {
						ArrayList<TextRequest> arrText = new ArrayList<TextRequest>();
						arrText.add(text);

						mapTextPerPage.put(text.getPage(), arrText);
					}
				}
			}

			Map<String, PdfFont> fonts = FontUtils.createFonts();
			if (stamps != null && stamps.size() > 0) {
				for (StampRequest stamp : stamps) {
					ImageData img = ImageDataFactory.create(Base64.decode(stamp.getStamp_data()));

					PdfPage page = pdfDoc.getPage(stamp.getPage());
					Logger.getLogger(PdfSignatureService.class.getName()).log(Level.INFO,
							"Start impress image with x [" + stamp.getX_axis() + "], y [" + stamp.getY_axis()
									+ "], height [" + stamp.getHeight() + "], width [" + stamp.getWidth() + "]",
							(Object) null);
					float x = mmToPoints(stamp.getX_axis(), page) + page.getCropBox().getX();

					float w = mmToPoints(stamp.getWidth(), page);
					float h = mmToPoints(stamp.getHeight(), page);

					float y = page.getPageSize().getHeight() - mmToPoints(stamp.getY_axis(), page)
							+ page.getCropBox().getY();

					Logger.getLogger(PdfSignatureService.class.getName()).log(Level.INFO,
							"Impress image with x [" + x + "], y [" + y + "], height [" + h + "], width [" + w + "]",
							(Object) null);

					PdfCanvas pdfCanvas = new PdfCanvas(page.newContentStreamAfter(),
							pdfDoc.getPage(stamp.getPage()).getResources(), pdfDoc);

					// Wrap old content in q/Q in order not to get unexpected results because of the
					// CTM
					page.newContentStreamBefore().getOutputStream().writeBytes(ByteUtils.getIsoBytes("q\n"));
					pdfCanvas.getContentStream().getOutputStream().writeBytes(ByteUtils.getIsoBytes("Q\n"));

					double angle = 0;
					double pageRotation = page.getRotation();
					if (pageRotation != 0) {
						if (pageRotation == 90) {
							pageRotation = (pageRotation * Math.PI / 180d);
							Rectangle pageSizeWithRotation = page.getPageSizeWithRotation();
							pdfCanvas.concatMatrix(AffineTransform.getRotateInstance(pageRotation,
									pageSizeWithRotation.getLeft() + pageSizeWithRotation.getWidth(),
									pageSizeWithRotation.getBottom() + pageSizeWithRotation.getWidth()));
							y += page.getPageSize().getHeight();
						} else if (pageRotation == 270) {
							pageRotation = (pageRotation * Math.PI / 180d);
							Rectangle pageSizeWithRotation = page.getPageSizeWithRotation();
							pdfCanvas.concatMatrix(AffineTransform.getRotateInstance(pageRotation,
									pageSizeWithRotation.getLeft() + pageSizeWithRotation.getWidth() / 2,
									pageSizeWithRotation.getBottom() + pageSizeWithRotation.getWidth() / 2));
							y -= (pageSizeWithRotation.getWidth() - pageSizeWithRotation.getHeight());
						} else if (pageRotation == 180) {
							// ファイルの幅から反転後のx，y座標を減算します，もう一つの印鑑の占位を減算します，2.833は長さとピクセルの変換割合です
							x = page.getPageSize().getWidth() - x - (stamp.getWidth() * 2.833f);
							y = page.getPageSize().getHeight() - y - (stamp.getHeight() * 2.833f);
							angle = 180 * Math.PI / 180d;
						}
					}

					if (angle != 0 || (stamp.getRotateAngle() != null && stamp.getRotateAngle() != 0)) {
						if (stamp.getRotateAngle() != null && stamp.getRotateAngle() != 0) {
							angle = (stamp.getRotateAngle() * Math.PI / 180d);
						}

						pdfCanvas.saveState();
						pdfCanvas.concatMatrix(AffineTransform.getRotateInstance(angle, x + w / 2, y + h / 2));
						pdfCanvas.addImage(img, x, y, h, false, false);
						pdfCanvas.restoreState();
					} else {
						pdfCanvas.addImage(img, x, y, h, false, false);
					}

					if (mapTextPerPage.containsKey(stamp.getPage())) {
						addTextToPage(stamp.getPage(), mapTextPerPage, page, pdfCanvas, fonts);
						mapTextPerPage.remove(stamp.getPage());
					}

					float x2 = x;
					float y2 = y;

					pageRotation = page.getRotation();
					if (pageRotation == 90 ) {
						y2 = mmToPoints(stamp.getX_axis(), page) + page.getCropBox().getX();
						x2 = mmToPoints(stamp.getY_axis(), page) - h + page.getCropBox().getY();
					}else if (pageRotation == 180){
						x2 = page.getPageSize().getWidth() - mmToPoints(stamp.getX_axis(), page) - w + page.getCropBox().getX();
						y2 = mmToPoints(stamp.getY_axis(), page) - h + page.getCropBox().getY();
					}else if (pageRotation == 270){
						x2 = page.getPageSize().getWidth() - mmToPoints(stamp.getY_axis(), page) + page.getCropBox().getX();
						y2 = page.getPageSize().getHeight() - mmToPoints(stamp.getX_axis(), page) - w + page.getCropBox().getY();
						float w2 = w;
						w = h;
						h = w2;
					}
					Rectangle linkLocation = new Rectangle(x2, y2, w, h);
					PdfAnnotation linkAnnotation = new PdfLinkAnnotation(linkLocation)
							.setHighlightMode(PdfAnnotation.HIGHLIGHT_INVERT)
							.setAction(PdfAction.createURI(stamp.getStamp_url(), false))
							.setBorder(new PdfArray(new float[] { 0, 0, 0 }));

					//注釈追加処理
					int pdf_annotation_flg =  stamp.getPdf_annotation_flg();
					addAnnotationToPDF(linkLocation, page, stamp, pdf_annotation_flg);

					page.addAnnotation(linkAnnotation);
				}
			}
			if (mapTextPerPage.size() > 0) {
				Set<Integer> pages = mapTextPerPage.keySet();
				for (Integer pageNo : pages) {

					PdfPage page = pdfDoc.getPage(pageNo);
					PdfCanvas pdfCanvas = new PdfCanvas(page.newContentStreamAfter(),
							pdfDoc.getPage(pageNo).getResources(), pdfDoc);
					// Wrap old content in q/Q in order not to get unexpected results because of the
					// CTM
					page.newContentStreamBefore().getOutputStream().writeBytes(ByteUtils.getIsoBytes("q\n"));
					pdfCanvas.getContentStream().getOutputStream().writeBytes(ByteUtils.getIsoBytes("Q\n"));

					double pageRotation = page.getRotation();
					if (pageRotation != 0) {
						if (pageRotation == 90) {
							pageRotation = (pageRotation * Math.PI / 180d);
							pdfCanvas.concatMatrix(AffineTransform.getRotateInstance(pageRotation,
									page.getPageSizeWithRotation().getLeft()
											+ page.getPageSizeWithRotation().getWidth(),
									page.getPageSizeWithRotation().getBottom()
											+ page.getPageSizeWithRotation().getWidth()));
						} else if (pageRotation == 270) {
							pageRotation = (pageRotation * Math.PI / 180d);
							pdfCanvas.concatMatrix(AffineTransform.getRotateInstance(pageRotation,
									page.getPageSizeWithRotation().getLeft()
											+ page.getPageSizeWithRotation().getWidth() / 2,
									page.getPageSizeWithRotation().getBottom()
											+ page.getPageSizeWithRotation().getWidth() / 2));
						} else {

						}

					}
					addTextToPage(pageNo, mapTextPerPage, page, pdfCanvas, fonts);
				}
			}

			if (appendedInputStream != null) {
				// Add pages from the second pdf document
				PdfDocument secondSourcePdf = new PdfDocument(new PdfReader(appendedInputStream));
				secondSourcePdf.copyPagesTo(1, secondSourcePdf.getNumberOfPages(), pdfDoc);
				secondSourcePdf.close();
			}

			pdfDoc.close();
			return true;
		} catch (Exception ex) {
			Logger.getLogger(PdfSignatureService.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}

	private void addAnnotationToPDF(Rectangle linkLocation, PdfPage page, StampRequest stamp, int pdf_annotation_flg) throws IOException {

		if(pdf_annotation_flg != 1){
			return;
		}

		String email =  stamp.getEmail();
		String stamp_time =  stamp.getStamp_time();
		String file_name =  stamp.getFile_name();
		String serial =  stamp.getSerial();
		PdfAnnotation TextAnnotation = new PdfTextAnnotation(linkLocation)
		.setOpacity(new PdfNumber(0))
		.setFlag(PdfAnnotation.LOCKED)
		.setContents(
			"メールアドレス\r\n"
			+ email
			+ "\r\n\r\n捺印日時\r\n"
			+ stamp_time
			+ "\r\n\r\nファイル名\r\n"
			+ file_name
			+ "\r\n\r\nシリアル\r\n"
			+ serial
		);
		page.addAnnotation(TextAnnotation);
	}

	private void addTextToPage(Integer pageNo, Map<Integer, ArrayList<TextRequest>> mapTextPerPage, PdfPage page,
			PdfCanvas pdfCanvas, Map<String, PdfFont> fonts) throws IOException {
		ArrayList<TextRequest> arrText = mapTextPerPage.get(pageNo);
		for (TextRequest text : arrText) {
			if (!StringUtils.isEmpty(text.getText())) {
				PdfFont font = fonts.get(text.getFontFamily());
				// fix PAC_5-286 コメント追加時の文字サイズが異なっている, use font size unit pt in PAC-USER,
				// convert again mm
				float fontSize = (float) Math.floor(text.getFontSize() / (1.333333333333333333f * 0.2645833333f));
				String[] strs = text.getText().split("\n");

				float leading = 0.9f * fontSize;
				PdfCanvas canvas = null;
				float x = mmToPoints(text.getX_axis(), page) + page.getCropBox().getX();
				float y = page.getPageSize().getHeight() - mmToPoints(text.getY_axis(), page) - leading
						+ page.getCropBox().getY();

				boolean saveState = false;
				if (page.getRotation() != 0) {
					if (page.getRotation() == 90) {
						y += page.getPageSize().getHeight();
					} else if (page.getRotation() == 270) {
						y -= (page.getPageSize().getHeight() - page.getPageSize().getWidth());
					} else if (page.getRotation() == 180) {
						// ファイルの幅から反転後のx，y座標を減算します，もう一つの印鑑の占位を減算します，2.833は長さとピクセルの変換割合です
						x = page.getPageSize().getWidth() - x;
						y = page.getPageSize().getHeight() - y;

						pdfCanvas.saveState();
						saveState = true;
						pdfCanvas.concatMatrix(AffineTransform.getRotateInstance(Math.PI, x, y));
					}
				}
				if (strs.length > 1) {
					canvas = pdfCanvas.beginText().moveText(x, y).setFontAndSize(font, fontSize)
							.setLeading(1.1f * fontSize);
				} else {
					canvas = pdfCanvas.beginText().moveText(x, y).setFontAndSize(font, fontSize)
							.setLeading(1.05f * fontSize);
				}
				canvas.setColor(ConvertColorValue.ConvertHexToRGB(text.getFontColor()), true);
				boolean isFirst = true;
				for (String str : strs) {
					if (isFirst) {
						canvas = canvas.showText(str);
						isFirst = false;
					} else {
						canvas = canvas.newlineShowText(str);
					}
				}
				canvas.endText().stroke();
				if (saveState) {
					pdfCanvas.restoreState();
				}
			}
		}
	}

	private static float mmToPoints(float mm, PdfPage page) {
		PdfNumber userUnit = page.getPdfObject().getAsNumber(PdfName.UserUnit);
		float userUnitValue = (userUnit == null) ? 72f : userUnit.floatValue();
		return (mm * userUnitValue) / 25.4f;
	}

	@Override
	@TrackExecutionTime
	public boolean signaturePdf(boolean isUsingTas, InputStream inputStream, OutputStream outputStream,
			InputStream keyStream, String password, String reason) {
		try {
			BouncyCastleProvider provider = new BouncyCastleProvider();
			Security.addProvider(provider);
			KeyStore ks = KeyStore.getInstance(KeyStore.getDefaultType());
			ks.load(keyStream, password.toCharArray());
			String alias = ks.aliases().nextElement();
			PrivateKey pk = (PrivateKey) ks.getKey(alias, password.toCharArray());
			Certificate[] chain = ks.getCertificateChain(alias);
			if (reason == null) {
				reason = "";
			}
			sign(isUsingTas, inputStream, outputStream, chain, pk, DigestAlgorithms.SHA256, provider.getName(),
					PdfSigner.CryptoStandard.CMS, reason, this.location);
			return true;
		} catch (Exception ex) {
			Logger.getLogger(PdfSignatureService.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}

	private void sign(boolean isUsingTas, InputStream inputStream, OutputStream outputStream, Certificate[] chain,
			PrivateKey pk, String digestAlgorithm, String provider, PdfSigner.CryptoStandard subfilter, String reason,
			String location) throws GeneralSecurityException, IOException {
		// Creating the reader and the signer
		PdfReader reader = new PdfReader(inputStream);
		PacSigner signer = new PacSigner(reader, outputStream, true);
		// Creating the appearance
		signer.getSignatureAppearance().setReason(reason).setLocation(location).setReuseAppearance(false);
		// Rectangle rect = new Rectangle(36, 648, 200, 100);
		// appearance.setPageRect(rect).setPageNumber(1);
		signer.setFieldName("signature " + new Date().getTime());
		// Creating the signature
		IExternalSignature pks = new PrivateKeySignature(pk, digestAlgorithm, provider);
		IExternalDigest digest = new BouncyCastleDigest();
		ITSAClient tsaClient = null;
		if (isUsingTas) {
			tsaClient = new TSAClientBouncyCastle(tasUrl, tasUsername, tasPassword);
		}
		signer.setSignatureEvent(new PdfSigner.ISignatureEvent() {

			@Override
			public void getSignatureDictionary(PdfSignature sig) {
				sig.getPdfObject().remove(PdfName.Location);
			}
		});
		signer.signDetached(digest, pks, chain, null, null, tsaClient, 0, subfilter);
	}

	@TrackExecutionTime
	public boolean enableLtv(boolean isUsingTas, InputStream inputStream, OutputStream outStream) throws IOException {
		PdfReader pdfReader = null;
		try {
			pdfReader = new PdfReader(inputStream);
			PdfWriter pdfWriter = new PdfWriter(outStream);
			PdfDocument pdfDocument = new PdfDocument(pdfReader, pdfWriter,
					new StampingProperties().preserveEncryption().useAppendMode());

			AdobeUtil adobeUtil = null;
			if (isUsingTas) {
				adobeUtil = new AdobeUtil(pdfDocument, this.mapCertificates, false, this.timestampCertificate);
			} else {
				adobeUtil = new AdobeUtil(pdfDocument, this.mapCertificates, false, null);
			}
			adobeUtil.enableLtv(this.customCrlClient);
			pdfDocument.close();

			return true;
		} catch (IOException | OperatorCreationException | GeneralSecurityException | StreamParsingException
				| OCSPException ex) {
			Logger.getLogger(PdfSignatureService.class.getName()).log(Level.SEVERE, null, ex);
		} finally {
			if (pdfReader != null) {
				pdfReader.close();
			}
		}
		return false;
	}

	@TrackExecutionTime
	public boolean signatureDTS(InputStream inputStream, OutputStream outStream, String reason) throws IOException{
		PdfReader pdfReader = null;
		try {
			ITSAClient tsaClient = new TSAClientBouncyCastle(tasUrl, tasUsername, tasPassword, 2*4096, TSAClientBouncyCastle.DEFAULTHASHALGORITHM);
			pdfReader = new PdfReader(inputStream);
			PdfSigner signer = new PdfSigner(pdfReader, outStream, new StampingProperties().useAppendMode());
			signer.setSignatureEvent(new PdfSigner.ISignatureEvent() {
				@Override
				public void getSignatureDictionary(PdfSignature sig) {
					if (reason != null) {
						sig.getPdfObject().put(PdfName.Reason, new PdfString(reason,  PdfEncodings.UNICODE_BIG));
					}
				}
			});
			signer.timestamp(tsaClient, null);
			return true;

		} catch (IOException | GeneralSecurityException ex) {
			Logger.getLogger(PdfSignatureService.class.getName()).log(Level.SEVERE, null, ex);
		} finally {
			if (pdfReader != null) {
				pdfReader.close();
			}
		}
		return false;
	}

	@TrackExecutionTime
	public boolean impressWithSignaturePdf(ArrayList<TextRequest> texts, ArrayList<StampRequest> stamps, InputStream inputStream, OutputStream impressOutputStream, InputStream keyStream, String password, String reason, boolean isUsingTas) {
		try {
			inputStream.mark(0);
			PdfDocument pdfDoc = new PdfDocument( new PdfReader(inputStream));

			Map<Integer, ArrayList<TextRequest>> mapTextPerPage = new HashMap<Integer, ArrayList<TextRequest>>();
			if (texts != null) {
				for (TextRequest text : texts) {
					if (mapTextPerPage.containsKey(text.getPage())) {
						mapTextPerPage.get(text.getPage()).add(text);
					} else {
						ArrayList<TextRequest> arrText = new ArrayList<TextRequest>();
						arrText.add(text);

						mapTextPerPage.put(text.getPage(), arrText);
					}
				}
			}

			ArrayList<Rectangle> stampPositions = new ArrayList<Rectangle>();

			ArrayList<TextRequest> textAddeds = new ArrayList<TextRequest>();
			ArrayList<Rectangle> textPositions = new ArrayList<Rectangle>();

			if (stamps != null && stamps.size() > 0) {
				for (StampRequest stamp : stamps) {
					PdfPage page = pdfDoc.getPage(stamp.getPage());
					Logger.getLogger(PdfSignatureService.class.getName()).log(Level.INFO,
							"Start impress image with x [" + stamp.getX_axis() + "], y [" + stamp.getY_axis()
									+ "], height [" + stamp.getHeight() + "], width [" + stamp.getWidth() + "]",
							(Object) null);
					float x = mmToPoints(stamp.getX_axis(), page) + page.getCropBox().getX();

					float w = mmToPoints(stamp.getWidth(), page);
					float h = mmToPoints(stamp.getHeight(), page);
					float y = page.getPageSize().getHeight() - mmToPoints(stamp.getY_axis(), page)
							+ page.getCropBox().getY();

					Logger.getLogger(PdfSignatureService.class.getName()).log(Level.INFO,
							"Impress image with x [" + x + "], y [" + y + "], height [" + h + "], width [" + w + "]",
							(Object) null);

					double pageRotation = page.getRotation();
					if (pageRotation == 90 ) {
						y = mmToPoints(stamp.getX_axis(), page) + page.getCropBox().getX();
						x = mmToPoints(stamp.getY_axis(), page) - h + page.getCropBox().getY();
					}else if (pageRotation == 180){
						x = page.getPageSize().getWidth() - mmToPoints(stamp.getX_axis(), page) - w + page.getCropBox().getX();
						y = mmToPoints(stamp.getY_axis(), page) - h + page.getCropBox().getY();
					}else if (pageRotation == 270){
						x = page.getPageSize().getWidth() - mmToPoints(stamp.getY_axis(), page) + page.getCropBox().getX();
						y = page.getPageSize().getHeight() - mmToPoints(stamp.getX_axis(), page) - w + page.getCropBox().getY();
					}

					Rectangle position = new Rectangle(x, y, w, h);

					stampPositions.add(position);

					if (mapTextPerPage.containsKey(stamp.getPage())) {
						addTextToPageWithSignature(textAddeds, textPositions, stamp.getPage(), mapTextPerPage, page);
						mapTextPerPage.remove(stamp.getPage());
					}
				}
			}
			if (mapTextPerPage.size() > 0) {
				Set<Integer> pages = mapTextPerPage.keySet();
				for (Integer pageNo : pages) {
					PdfPage page = pdfDoc.getPage(pageNo);
					addTextToPageWithSignature(textAddeds, textPositions, pageNo, mapTextPerPage, page);
				}
			}

			pdfDoc.close();

			BouncyCastleProvider provider = new BouncyCastleProvider();
			Security.addProvider(provider);
			KeyStore ks = KeyStore.getInstance(KeyStore.getDefaultType());
			ks.load(keyStream, password.toCharArray());
			String alias = ks.aliases().nextElement();
			PrivateKey pk = (PrivateKey) ks.getKey(alias, password.toCharArray());
			Certificate[] chain = ks.getCertificateChain(alias);
			if (reason == null) {
				reason = "";
			}

			ITSAClient tsaClient = null;
			if (isUsingTas) {
				tsaClient = new TSAClientBouncyCastle(tasUrl, tasUsername, tasPassword);
			}

			IExternalSignature pks = new PrivateKeySignature(pk, DigestAlgorithms.SHA256, provider.getName());
			IExternalDigest digest = new BouncyCastleDigest();

			inputStream.reset();
			PdfReader pdfReader = new PdfReader(inputStream);
			PacSigner signer = new PacSigner(pdfReader, impressOutputStream, true);

			signer.getSignatureAppearance().setReason(reason).setLocation(location).setReuseAppearance(false);
			signer.signDetached(digest, pks, chain, null, null, tsaClient, 0, PdfSigner.CryptoStandard.CMS, stamps, stampPositions, textAddeds, textPositions);
			return true;
		} catch (Exception ex) {
			Logger.getLogger(PdfSignatureService.class.getName()).log(Level.SEVERE, null, ex);
		}

		return false;
	}

	private void addTextToPageWithSignature(ArrayList<TextRequest> textAddeds, ArrayList<Rectangle> textPositions, Integer pageNo, Map<Integer, ArrayList<TextRequest>> mapTextPerPage, PdfPage page) throws IOException, FontFormatException, GeneralSecurityException {
		ArrayList<TextRequest> arrText = mapTextPerPage.get(pageNo);
		for (TextRequest text : arrText) {
			if (!StringUtils.isEmpty(text.getText())) {
				PdfFont font = PdfFontFactory.createRegisteredFont(text.getFontFamily(), PdfEncodings.IDENTITY_H);

				// fix PAC_5-286 コメント追加時の文字サイズが異なっている, use font size unit pt in PAC-USER,
				// convert again mm
				float fontSize = (float) Math.floor(text.getFontSize() / (1.333333333333333333f * 0.2645833333f));
				String[] strs = text.getText().split("\n");
				float w = 0;
				float h = 1.1f * fontSize * strs.length;
				for (String str : strs) {
					float strWidth = font.getWidth(str, fontSize);
					if (w < strWidth) {
						w = strWidth;
					}
				}

				float x = mmToPoints(text.getX_axis(), page) + page.getCropBox().getX();
				float y = page.getPageSize().getHeight() - mmToPoints(text.getY_axis(), page) - h + page.getCropBox().getY();

				double pageRotation = page.getRotation();
				if (pageRotation == 90 ) {
					x = mmToPoints(text.getY_axis(), page) + page.getCropBox().getY();
					y = mmToPoints(text.getX_axis(), page) + page.getCropBox().getX();
				}else if (pageRotation == 180){
					x = page.getPageSize().getWidth() - mmToPoints(text.getX_axis(), page) + page.getCropBox().getX() - w;
					y = mmToPoints(text.getY_axis(), page) + page.getCropBox().getY() ;
				}else if (pageRotation == 270){
					x = page.getPageSize().getWidth() - mmToPoints(text.getY_axis(), page) - h + page.getCropBox().getX();
					y = page.getPageSize().getHeight() - mmToPoints(text.getX_axis(), page) - w  + page.getCropBox().getY();
				}

				Rectangle position = new Rectangle(x, y, w, h);
				text.setFontSize(fontSize);
				text.setFont(font);
				textAddeds.add(text);
				textPositions.add(position);
			}
		}
	}
}
