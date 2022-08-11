package com.hanko.app.controller;

import java.io.ByteArrayInputStream;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

import javax.validation.Valid;

import com.itextpdf.kernel.pdf.*;
import com.itextpdf.kernel.pdf.annot.PdfAnnotation;
import com.itextpdf.kernel.pdf.annot.PdfLinkAnnotation;
import org.apache.tomcat.util.http.fileupload.IOUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.core.io.InputStreamResource;
import org.springframework.core.io.Resource;
import org.springframework.util.FastByteArrayOutputStream;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import com.hanko.app.bean.PdfRequest;
import com.hanko.app.bean.PdfResponse;
import com.hanko.app.bean.SignatureRequest;
import com.hanko.app.bean.SignatureResponse;
import com.hanko.app.bean.StampRequest;
import com.hanko.app.bean.TextRequest;
import com.hanko.app.service.IPdfSignatureService;
import com.hanko.app.utils.TrackExecutionTime;
import com.itextpdf.io.codec.Base64;
import org.springframework.web.multipart.MultipartFile;

@RestController
public class PdfController {
	@Autowired
	IPdfSignatureService pdfSignatureService;

	@Value("${signature.key.file}")
	private Resource defaultSignatureFileResource;

	@Value("${signature.key.password}")
	private String defaultSignaturePassword;

	@PostMapping("/api/signatureAndImpress")
	@TrackExecutionTime
	public SignatureResponse signatureAndImpress(@Valid @RequestBody SignatureRequest signatureRequest) {
		SignatureResponse signatureResponse = new SignatureResponse();
		ArrayList<PdfResponse> pdfResponse = new ArrayList<PdfResponse>();

		if (signatureRequest.getData() != null) {
			boolean isResponseSuccess = true;
			try {
				boolean isSignature = signatureRequest.getSignature();

				String signaturePassword = null;
				if (StringUtils.isEmpty(signatureRequest.getSignatureKeyFile())) {
					signaturePassword = defaultSignaturePassword;
				} else {
					signaturePassword = signatureRequest.getSignatureKeyPassword();
				}

				for (PdfRequest request : signatureRequest.getData()) {
					if (!StringUtils.isEmpty(request.getPdf_data())) {
						Logger.getLogger(PdfController.class.getName()).log(Level.INFO,
								"Impress for file " + request.getCircular_document_id(), (Object) null);
						PdfResponse response = new PdfResponse();
						response.setCircular_document_id(request.getCircular_document_id());

						Logger.getLogger(PdfController.class.getName()).log(Level.INFO, "Decode PDF Data",
								(Object) null);
						byte[] input = Base64.decode(request.getPdf_data());
						InputStream inputStream = new ByteArrayInputStream(input);

						Logger.getLogger(PdfController.class.getName()).log(Level.INFO, "Decode PDF Data appended",
								(Object) null);
						InputStream appendedInputStream = null;
						if (request.getAppend_pdf_data() != null) {
							byte[] appendedInput = Base64.decode(request.getAppend_pdf_data());
							appendedInputStream = new ByteArrayInputStream(appendedInput);
						}
						FastByteArrayOutputStream impressOutputStream = new FastByteArrayOutputStream();

						Logger.getLogger(PdfController.class.getName()).log(Level.INFO, "Impress PDF", (Object) null);
						boolean success = true;
						boolean hasEnableLtv = false;
						boolean addedSignature = false;
						boolean isUsingTas = (!signatureRequest.isUsingNewSignatureForTas()) && request.isUsingTas();
						boolean hasTexts = request.getTexts() != null && request.getTexts().size() > 0;
						boolean hasStamps = request.getStamps() != null && request.getStamps().size() > 0;
						if (hasTexts || hasStamps || appendedInputStream != null) {
							if (isSignature
									&& (hasTexts || hasStamps)
									&& appendedInputStream == null) {
								InputStream signatureKeyInputStream = null;
								if (StringUtils.isEmpty(signatureRequest.getSignatureKeyFile())) {
									signatureKeyInputStream = defaultSignatureFileResource.getInputStream();
									hasEnableLtv = true;
								} else {
									signatureKeyInputStream = new FileInputStream(
											signatureRequest.getSignatureKeyFile());
								}

								if (hasStamps) {
									ArrayList<StampRequest> tmpStamp = new ArrayList<StampRequest>();
									Iterator<StampRequest> stampIterator = request.getStamps().iterator();
									while (stampIterator.hasNext()) {
										tmpStamp.add(stampIterator.next());
										success = pdfSignatureService.impressWithSignaturePdf(null, tmpStamp,
												inputStream, impressOutputStream, signatureKeyInputStream,
												signaturePassword, signatureRequest.getSignatureReason(), isUsingTas);
										tmpStamp.clear();
										if (success) {
											if (stampIterator.hasNext() || hasTexts) {
												inputStream = new ByteArrayInputStream(impressOutputStream.toByteArray());
												impressOutputStream = new FastByteArrayOutputStream();
												if (StringUtils.isEmpty(signatureRequest.getSignatureKeyFile())) {
													signatureKeyInputStream = defaultSignatureFileResource.getInputStream();
												} else {
													signatureKeyInputStream = new FileInputStream(
															signatureRequest.getSignatureKeyFile());
												}
											}
										} else {
											break;
										}
									}
								}

								if (success && hasTexts) {
									ArrayList<TextRequest> tmpText = new ArrayList<TextRequest>();

									Iterator<TextRequest> textIterator = request.getTexts().iterator();
									while (textIterator.hasNext()) {
										tmpText.add(textIterator.next());
										success = pdfSignatureService.impressWithSignaturePdf(tmpText, null,
												inputStream, impressOutputStream, signatureKeyInputStream,
												signaturePassword, signatureRequest.getSignatureReason(), isUsingTas);
										tmpText.clear();
										if (success) {
											if (textIterator.hasNext()) {
												inputStream = new ByteArrayInputStream(impressOutputStream.toByteArray());
												impressOutputStream = new FastByteArrayOutputStream();
												if (StringUtils.isEmpty(signatureRequest.getSignatureKeyFile())) {
													signatureKeyInputStream = defaultSignatureFileResource.getInputStream();
												} else {
													signatureKeyInputStream = new FileInputStream(
															signatureRequest.getSignatureKeyFile());
												}
											}
										} else {
											break;
										}
									}
								}
								addedSignature = true;
							} else {
								success = pdfSignatureService.impressPdf(request.getTexts(), request.getStamps(),
										inputStream, impressOutputStream, appendedInputStream);
							}
						} else {
							IOUtils.copyLarge(inputStream, impressOutputStream);
						}
						if (success) {
							if (isSignature || request.isUsingTas()) {
								FastByteArrayOutputStream signatureOutputStream = null;
								if (addedSignature) {
									signatureOutputStream = impressOutputStream;
								} else {
									InputStream signatureInputStream = new ByteArrayInputStream(
											impressOutputStream.toByteArray());
									signatureOutputStream = new FastByteArrayOutputStream();

									Logger.getLogger(PdfController.class.getName()).log(Level.INFO, "Sign PDF",
											(Object) null);

									InputStream signatureKeyInputStream = null;
									if (StringUtils.isEmpty(signatureRequest.getSignatureKeyFile())) {
										signatureKeyInputStream = defaultSignatureFileResource.getInputStream();
										hasEnableLtv = true;
									} else {
										signatureKeyInputStream = new FileInputStream(
												signatureRequest.getSignatureKeyFile());
									}
									success = pdfSignatureService.signaturePdf(isUsingTas, signatureInputStream,
											signatureOutputStream, signatureKeyInputStream, signaturePassword,
											signatureRequest.getSignatureReason());
								}
								FastByteArrayOutputStream finalSignatureOutputStream = null;
								if (signatureRequest.isUsingNewSignatureForTas()) {
									InputStream signatureInputStream = new ByteArrayInputStream(
											signatureOutputStream.toByteArray());
									finalSignatureOutputStream = new FastByteArrayOutputStream();

									InputStream signatureKeyInputStream = null;
									if (StringUtils.isEmpty(signatureRequest.getSignatureKeyFile())) {
										signatureKeyInputStream = defaultSignatureFileResource.getInputStream();
										hasEnableLtv = true;
									} else {
										signatureKeyInputStream = new FileInputStream(
												signatureRequest.getSignatureKeyFile());
									}
									success = pdfSignatureService.signaturePdf(request.isUsingTas(),
											signatureInputStream, finalSignatureOutputStream, signatureKeyInputStream,
											signaturePassword, signatureRequest.getTimestampSignatureReason());
								} else {
									finalSignatureOutputStream = signatureOutputStream;
								}

								if (success) {
									String pdfData = null;
									FastByteArrayOutputStream enableLtvOutputStream = null;
									if (hasEnableLtv) {
										InputStream enableLtvInputStream = new ByteArrayInputStream(
												finalSignatureOutputStream.toByteArray());
										enableLtvOutputStream = new FastByteArrayOutputStream();

										success = pdfSignatureService.enableLtv(request.isUsingTas() || request.isUsingDTS(), enableLtvInputStream, enableLtvOutputStream);
										if (!success) {
											Logger.getLogger(PdfController.class.getName()).log(Level.SEVERE, "Process to enable LTV is failed!", (Object) null);
											isResponseSuccess = false;
											break;
										}
									} else {
										enableLtvOutputStream = finalSignatureOutputStream;
									}

									FastByteArrayOutputStream dtsOutputStream = null;
									if (request.isUsingDTS()) {
										InputStream dtsInputStream = new ByteArrayInputStream(
												enableLtvOutputStream.toByteArray());
										dtsOutputStream = new FastByteArrayOutputStream();

										success = pdfSignatureService.signatureDTS(dtsInputStream, dtsOutputStream, signatureRequest.getDocumentTimestampSignatureReason());
										if (!success) {
											Logger.getLogger(PdfController.class.getName()).log(Level.SEVERE, "Process to siganture DTS is failed!", (Object) null);
											isResponseSuccess = false;
											break;
										}
									}else {
										dtsOutputStream = enableLtvOutputStream;
									}
									pdfData = read(dtsOutputStream);
									response.setPdf_data(pdfData);
								}
							} else {
								String pdfData = read(impressOutputStream);
								response.setPdf_data(pdfData);
							}
							pdfResponse.add(response);
						} else {
							isResponseSuccess = false;
							break;
						}
					}
				}
			} catch (Exception ex) {
				Logger.getLogger(PdfController.class.getName()).log(Level.SEVERE, null, ex);
				isResponseSuccess = false;
			}
			if (!isResponseSuccess) {
				pdfResponse.clear();
			}
			signatureResponse.setSuccess(isResponseSuccess);
		}
		signatureResponse.setData(pdfResponse);
		return signatureResponse;
	}

	private String read(FastByteArrayOutputStream stream) {
		return Base64.encodeBytes(stream.toByteArray());
	}

	/**
	 * 注釈を削除して黒ずみ現象を解消
	 * @param file InputStreamSource
	 * @return
	 * @throws IOException
	 */
	@PostMapping("/api/delPdfAnnotation")
	public Resource delPdfAnnotation(@RequestParam("file") MultipartFile file) throws IOException {

		FastByteArrayOutputStream outputStream = new FastByteArrayOutputStream();

		PdfDocument pdfDoc = new PdfDocument(new PdfReader(file.getInputStream()), new PdfWriter(outputStream));
		// このPDFをアップロードされても、シヤチハタクラウドのPDFプレビュー画面で黒塗りにならないようにする (PAC_5-1573)
		// → 注釈の削除で対応、対応できる理由は不明、なお存在しても問題がない(黒塗りにならない)注釈も削除する
		for (int i = 1; i <= pdfDoc.getNumberOfPages(); i++) {
			PdfPage page = pdfDoc.getPage(i);
			List<PdfAnnotation> annotations = page.getAnnotations();
			for (PdfAnnotation annotation : annotations) {
				if (annotation.getSubtype().equals(PdfName.Link)){
					PdfLinkAnnotation annotation1 = (PdfLinkAnnotation) annotation;
					if (annotation1.getAction() != null && annotation1.getAction().containsValue(PdfName.Action) && !annotation1.getAction().containsKey(PdfName.IsMap)){
						page.removeAnnotation(annotation);
					}
				}
			}
		}

		pdfDoc.close();
		return new InputStreamResource(new ByteArrayInputStream(outputStream.toByteArray()));
	}
}
