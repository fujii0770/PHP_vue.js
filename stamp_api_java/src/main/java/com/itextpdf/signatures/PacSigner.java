package com.itextpdf.signatures;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.security.GeneralSecurityException;
import java.security.PrivateKey;
import java.security.cert.Certificate;
import java.security.cert.X509Certificate;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Collection;
import java.util.HashMap;
import java.util.Map;

import com.hanko.app.utils.ConvertColorValue;
import org.bouncycastle.asn1.esf.SignaturePolicyIdentifier;
import org.springframework.util.StringUtils;

import com.hanko.app.bean.StampRequest;
import com.hanko.app.bean.TextRequest;
import com.itextpdf.forms.PdfAcroForm;
import com.itextpdf.forms.fields.PdfFormField;
import com.itextpdf.forms.fields.PdfSignatureFormField;
import com.itextpdf.io.codec.Base64;
import com.itextpdf.io.image.ImageData;
import com.itextpdf.io.image.ImageDataFactory;
import com.itextpdf.io.util.FileUtil;
import com.itextpdf.kernel.PdfException;
import com.itextpdf.kernel.font.PdfFont;
import com.itextpdf.kernel.geom.AffineTransform;
import com.itextpdf.kernel.geom.Rectangle;
import com.itextpdf.kernel.pdf.PdfArray;
import com.itextpdf.kernel.pdf.PdfDate;
import com.itextpdf.kernel.pdf.PdfDeveloperExtension;
import com.itextpdf.kernel.pdf.PdfDictionary;
import com.itextpdf.kernel.pdf.PdfLiteral;
import com.itextpdf.kernel.pdf.PdfName;
import com.itextpdf.kernel.pdf.PdfOutputStream;
import com.itextpdf.kernel.pdf.PdfPage;
import com.itextpdf.kernel.pdf.PdfReader;
import com.itextpdf.kernel.pdf.PdfString;
import com.itextpdf.kernel.pdf.action.PdfAction;
import com.itextpdf.kernel.pdf.annot.PdfAnnotation;
import com.itextpdf.kernel.pdf.annot.PdfWidgetAnnotation;
import com.itextpdf.kernel.pdf.canvas.PdfCanvas;
import com.itextpdf.kernel.pdf.xobject.PdfFormXObject;

public class PacSigner extends PdfSigner{

	public PacSigner(PdfReader reader, OutputStream outputStream, boolean append) throws IOException {
        super(reader, outputStream, null, append);
    }

    /**
     * Creates a PdfSigner instance. Uses a {@link java.io.ByteArrayOutputStream} instead of a temporary file.
     *
     * @param reader       PdfReader that reads the PDF file
     * @param outputStream OutputStream to write the signed PDF file
     * @param path         File to which the output is temporarily written
     * @param append       boolean to indicate whether the signing should happen in append mode or not
     * @throws IOException
     */
    public PacSigner(PdfReader reader, OutputStream outputStream, String path, boolean append) throws IOException {
    	super(reader, outputStream, path, append);
    }

    /**
     * Signs the document using the detached mode, CMS or CAdES equivalent.
     * <br><br>
     * NOTE: This method closes the underlying pdf document. This means, that current instance
     * of PdfSigner cannot be used after this method call.
     *
     * @param externalSignature the interface providing the actual signing
     * @param chain             the certificate chain
     * @param crlList           the CRL list
     * @param ocspClient        the OCSP client
     * @param tsaClient         the Timestamp client
     * @param externalDigest    an implementation that provides the digest
     * @param estimatedSize     the reserved size for the signature. It will be estimated if 0
     * @param sigtype           Either Signature.CMS or Signature.CADES
     * @throws IOException
     * @throws GeneralSecurityException
     */
    public void signDetached(IExternalDigest externalDigest, IExternalSignature externalSignature, Certificate[] chain, Collection<ICrlClient> crlList, IOcspClient ocspClient,
                             ITSAClient tsaClient, int estimatedSize, CryptoStandard sigtype,
                             ArrayList<StampRequest> stamps, ArrayList<Rectangle> stampPositions, ArrayList<TextRequest> textAddeds, ArrayList<Rectangle> textPositions) throws IOException, GeneralSecurityException {
        signDetached(externalDigest, externalSignature, chain, crlList, ocspClient, tsaClient, estimatedSize, sigtype, (SignaturePolicyIdentifier)null, stamps, stampPositions, textAddeds, textPositions);
    }

    /**
     * Signs the document using the detached mode, CMS or CAdES equivalent.
     * <br><br>
     * NOTE: This method closes the underlying pdf document. This means, that current instance
     * of PdfSigner cannot be used after this method call.
     *
     * @param externalSignature the interface providing the actual signing
     * @param chain             the certificate chain
     * @param crlList           the CRL list
     * @param ocspClient        the OCSP client
     * @param tsaClient         the Timestamp client
     * @param externalDigest    an implementation that provides the digest
     * @param estimatedSize     the reserved size for the signature. It will be estimated if 0
     * @param sigtype           Either Signature.CMS or Signature.CADES
     * @param signaturePolicy the signature policy (for EPES signatures)
     * @throws IOException
     * @throws GeneralSecurityException
     */
    public void signDetached(IExternalDigest externalDigest, IExternalSignature externalSignature, Certificate[] chain, Collection<ICrlClient> crlList, IOcspClient ocspClient,
                             ITSAClient tsaClient, int estimatedSize, CryptoStandard sigtype, SignaturePolicyIdentifier signaturePolicy,
                             ArrayList<StampRequest> stamps, ArrayList<Rectangle> stampPositions, ArrayList<TextRequest> textAddeds, ArrayList<Rectangle> textPositions) throws IOException, GeneralSecurityException {
        if (closed) {
            throw new PdfException(PdfException.ThisInstanceOfPdfSignerAlreadyClosed);
        }

        Collection<byte[]> crlBytes = null;
        int i = 0;
        while (crlBytes == null && i < chain.length)
            crlBytes = processCrl(chain[i++], crlList);
        if (estimatedSize == 0) {
            estimatedSize = 8192;
            if (crlBytes != null) {
                for (byte[] element : crlBytes) {
                    estimatedSize += element.length + 10;
                }
            }
            if (ocspClient != null)
                estimatedSize += 4192;
            if (tsaClient != null)
                estimatedSize += 4192;
        }
        PdfSignatureAppearance appearance = getSignatureAppearance();
        appearance.setCertificate(chain[0]);
        if (sigtype == CryptoStandard.CADES) {
            addDeveloperExtension(PdfDeveloperExtension.ESIC_1_7_EXTENSIONLEVEL2);
        }
        PdfSignature dic = new PdfSignature(PdfName.Adobe_PPKLite, sigtype == CryptoStandard.CADES ? PdfName.ETSI_CAdES_DETACHED : PdfName.Adbe_pkcs7_detached);
        dic.setReason(appearance.getReason());
        dic.setLocation(appearance.getLocation());
        dic.setSignatureCreator(appearance.getSignatureCreator());
        dic.setContact(appearance.getContact());
        dic.setDate(new PdfDate(getSignDate())); // time-stamp will over-rule this
        cryptoDictionary = dic;

        Map<PdfName, Integer> exc = new HashMap<>();
        exc.put(PdfName.Contents, estimatedSize * 2 + 2);
        preClose(exc, stamps, stampPositions, textAddeds, textPositions);

        String hashAlgorithm = externalSignature.getHashAlgorithm();
        PdfPKCS7 sgn = new PdfPKCS7((PrivateKey) null, chain, hashAlgorithm, null, externalDigest, false);
        if (signaturePolicy != null) {
            sgn.setSignaturePolicy(signaturePolicy);
        }
        InputStream data = getRangeStream();
        byte[] hash = DigestAlgorithms.digest(data, SignUtils.getMessageDigest(hashAlgorithm, externalDigest));
        byte[] ocsp = null;
        if (chain.length >= 2 && ocspClient != null) {
            ocsp = ocspClient.getEncoded((X509Certificate) chain[0], (X509Certificate) chain[1], null);
        }
        byte[] sh = sgn.getAuthenticatedAttributeBytes(hash, ocsp, crlBytes, sigtype);
        byte[] extSignature = externalSignature.sign(sh);
        sgn.setExternalDigest(extSignature, null, externalSignature.getEncryptionAlgorithm());

        byte[] encodedSig = sgn.getEncodedPKCS7(hash, tsaClient, ocsp, crlBytes, sigtype);

        if (estimatedSize < encodedSig.length)
            throw new IOException("Not enough space");

        byte[] paddedSig = new byte[estimatedSize];
        System.arraycopy(encodedSig, 0, paddedSig, 0, encodedSig.length);

        PdfDictionary dic2 = new PdfDictionary();
        dic2.put(PdfName.Contents, new PdfString(paddedSig).setHexWriting(true));
        close(dic2);

        closed = true;
    }

    /**
     * This is the first method to be called when using external signatures. The general sequence is:
     * preClose(), getDocumentBytes() and close().
     * <p>
     * <CODE>exclusionSizes</CODE> must contain at least
     * the <CODE>PdfName.CONTENTS</CODE> key with the size that it will take in the
     * document. Note that due to the hex string coding this size should be byte_size*2+2.
     *
     * @param exclusionSizes Map with names and sizes to be excluded in the signature
     *                       calculation. The key is a PdfName and the value an Integer. At least the /Contents must be present
     * @throws IOException on error
     */
    protected void preClose(Map<PdfName, Integer> exclusionSizes, ArrayList<StampRequest> stamps, ArrayList<Rectangle> stampPositions, ArrayList<TextRequest> textAddeds, ArrayList<Rectangle> textPositions) throws IOException {
        if (preClosed) {
            throw new PdfException(PdfException.DocumentAlreadyPreClosed);
        }

        // TODO: add mergeVerification functionality

        preClosed = true;
        PdfAcroForm acroForm = PdfAcroForm.getAcroForm(document, true);
        acroForm.setSignatureFlags(PdfAcroForm.SIGNATURE_EXIST | PdfAcroForm.APPEND_ONLY);

        if (cryptoDictionary == null) {
            throw new PdfException(PdfException.NoCryptoDictionaryDefined);
        }

        cryptoDictionary.getPdfObject().makeIndirect(document);

        int positionIdx = 0;
        PdfDictionary pdfDictionary = cryptoDictionary.getPdfObject();
        pdfDictionary.remove(PdfName.Location);
        if (stamps != null) {
            for(StampRequest stamp: stamps ) {
    			Rectangle stampPosition = stampPositions.get(positionIdx++);
    			PdfPage page = document.getPage(stamp.getPage());

    			PdfFormXObject napp = getImageAppearance(page, stampPosition.getWidth(), stampPosition.getHeight(), stamp.getRotateAngle(), stamp.getStamp_data(), stamp.getStamp_data_rotated());
    			PdfSignatureFormField sigField = createSignatureFormField(stampPosition, pdfDictionary, page, napp, stamp.getStamp_url(), true);
    			acroForm.addField(sigField, page);
            }
        }

        positionIdx = 0;
        if (textAddeds != null) {
            for(TextRequest text: textAddeds ) {
            	Rectangle textPosition = textPositions.get(positionIdx++);
    			PdfPage page = document.getPage(text.getPage());

                PdfFormXObject napp = getTextAppearance(page, textPosition.getWidth(), textPosition.getHeight(), text.getText(), text.getFont(), text.getFontSize(), text);
    			PdfSignatureFormField sigField = createSignatureFormField(textPosition, pdfDictionary, page, napp, null, true);
                acroForm.addField(sigField, page);
            }
        }

        if (acroForm.getPdfObject().isIndirect()) {
            acroForm.setModified();
        } else {
            //Acroform dictionary is a Direct dictionary,
            //for proper flushing, catalog needs to be marked as modified
            document.getCatalog().setModified();
        }

        exclusionLocations = new HashMap<>();

        PdfLiteral lit = new PdfLiteral(80);
        exclusionLocations.put(PdfName.ByteRange, lit);
        cryptoDictionary.put(PdfName.ByteRange, lit);
        for (Map.Entry<PdfName, Integer> entry : exclusionSizes.entrySet()) {
            PdfName key = entry.getKey();
            lit = new PdfLiteral((int) entry.getValue());
            exclusionLocations.put(key, lit);
            cryptoDictionary.put(key, lit);
        }
        if (certificationLevel > 0) {
            addDocMDP(cryptoDictionary);
        }
        if (signatureEvent != null) {
            signatureEvent.getSignatureDictionary(cryptoDictionary);
        }

        if (certificationLevel > 0) {
            // add DocMDP entry to root
            PdfDictionary docmdp = new PdfDictionary();
            docmdp.put(PdfName.DocMDP, cryptoDictionary.getPdfObject());
            document.getCatalog().put(PdfName.Perms, docmdp);
            document.getCatalog().setModified();
        }
        cryptoDictionary.getPdfObject().flush(false);
        document.close();

        range = new long[exclusionLocations.size() * 2];
        long byteRangePosition = exclusionLocations.get(PdfName.ByteRange).getPosition();
        exclusionLocations.remove(PdfName.ByteRange);
        int idx = 1;
        for (PdfLiteral lit1 : exclusionLocations.values()) {
            long n = lit1.getPosition();
            range[idx++] = n;
            range[idx++] = lit1.getBytesCount() + n;
        }
        Arrays.sort(range, 1, range.length - 1);
        for (int k = 3; k < range.length - 2; k += 2)
            range[k] -= range[k - 1];

        if (tempFile == null) {
            bout = temporaryOS.toByteArray();
            range[range.length - 1] = bout.length - range[range.length - 2];
            ByteArrayOutputStream bos = new ByteArrayOutputStream();
            PdfOutputStream os = new PdfOutputStream(bos);
            os.write('[');
            for (int k = 0; k < range.length; ++k) {
                os.writeLong(range[k]).write(' ');
            }
            os.write(']');
            System.arraycopy(bos.toByteArray(), 0, bout, (int) byteRangePosition, (int)bos.size());
            os.close();
        } else {
            try {
                raf = FileUtil.getRandomAccessFile(tempFile);
                long len = raf.length();
                range[range.length - 1] = len - range[range.length - 2];
                ByteArrayOutputStream bos = new ByteArrayOutputStream();
                PdfOutputStream os = new PdfOutputStream(bos);
                os.write('[');
                for (int k = 0; k < range.length; ++k) {
                    os.writeLong(range[k]).write(' ');
                }
                os.write(']');
                raf.seek(byteRangePosition);
                raf.write(bos.toByteArray(), 0, (int) bos.size());
                os.close();
            } catch (IOException e) {
                try {
                    raf.close();
                } catch (Exception ignored) {
                }
                try {
                    tempFile.delete();
                } catch (Exception ignored) {
                }
                throw e;
            }
        }
    }

    private PdfSignatureFormField createSignatureFormField(Rectangle position, PdfDictionary pdfDictionary, PdfPage page, PdfFormXObject napp, String url, boolean processRotation) {
        Rectangle rotated = null;
    	if (processRotation) {
        	int rotation = page.getRotation();
            if (rotation == 90 || rotation == 270) {
            	rotated = new Rectangle(position.getX(), position.getY(), position.getHeight(), position.getWidth());
            }else {
            	rotated = position;
            }
    	}else {
        	rotated = position;
    	}
    	PdfWidgetAnnotation widget = new PdfWidgetAnnotation(rotated);
        widget.setFlags(PdfAnnotation.PRINT | PdfAnnotation.LOCKED);
        if (!StringUtils.isEmpty(url)) {
        	widget.setAction(PdfAction.createURI(url, false));
        }

        PdfSignatureFormField sigField = PdfFormField.createSignature(document);
        sigField.setFieldName("signature " + System.currentTimeMillis() + String.valueOf((int)(Math.random()*1000)));
        sigField.put(PdfName.V, pdfDictionary);
        sigField.addKid(widget);

        widget.setPage(page);
        PdfDictionary ap = widget.getAppearanceDictionary();

        if (ap == null) {
            ap = new PdfDictionary();
            widget.put(PdfName.AP, ap);
        }
        ap.put(PdfName.N, napp.getPdfObject());
        return sigField;
    }

    private PdfFormXObject getImageAppearance(PdfPage page, float width, float height, Float rotateAngle, String imgData, Boolean isImageRotated) {
        return getAppearance(page, width, height, rotateAngle, imgData, null, null, 0f, isImageRotated, null);
    }

    private PdfFormXObject getTextAppearance(PdfPage page, float width, float height, String text, PdfFont font, float fontSize, TextRequest textRequest) {
        return getAppearance(page, width, height, 0f, null, text, font, fontSize, null, textRequest);
    }

    private PdfFormXObject getAppearance(PdfPage page, float width, float height, Float rotateAngle, String imgData, String text, PdfFont font, float fontSize, Boolean isImageRotated, TextRequest textRequest) {
        int rotation = page.getRotation();
        Rectangle rotated = null;
        if (rotation == 90 || rotation == 270) {
        	rotated = new Rectangle(height, width);
        } else {
        	rotated = new Rectangle(width, height);
        }

        PdfFormXObject topLayer = new PdfFormXObject(rotated);
        topLayer.makeIndirect(document);

        PdfCanvas canvas = new PdfCanvas(topLayer, document);

        if (rotation == 90) {
            canvas.concatMatrix(0, 1, -1, 0, height, 0);
        } else if (rotation == 180) {
            canvas.concatMatrix(-1, 0, 0, -1, width, height);
        } else if (rotation == 270) {
            canvas.concatMatrix(0, -1, 1, 0, 0, width);
        }

        if (imgData != null) {
            ImageData img = ImageDataFactory.create(Base64.decode(imgData));
            canvas.addImage(img, 0, 0, height, false, false);
        }
        if (text != null) {
    		String[] strs = text.split("\n");
    		float leading = 0.85f * fontSize;
    		canvas = canvas.beginText().moveText(0, height - leading).setFontAndSize(font, fontSize)
    				.setLeading(fontSize);
    		boolean isFirst = true;
            if (textRequest != null) canvas.setColor(ConvertColorValue.ConvertHexToRGB(textRequest.getFontColor()), true);
    		for (String str : strs) {
    			if (isFirst) {
    				canvas = canvas.showText(str);
    				isFirst = false;
    			} else {
    				canvas = canvas.newlineShowText(str);
    			}
    		}
    		canvas.endText().stroke();
        }

		PdfFormXObject napp = new PdfFormXObject(rotated);
		if (rotateAngle != null && rotateAngle != 0 && (isImageRotated == null || !isImageRotated)) {
			// Rotate the square stamp in Java
			double angle = (rotateAngle * Math.PI / 180d);
			float[] matrix = new float[6];
            AffineTransform.getRotateInstance(angle, width/2, height/2).getMatrix(matrix);
        	napp.put(PdfName.Matrix, new PdfArray(matrix));
		}
        napp.makeIndirect(document);
        napp.getResources().addForm(topLayer, new PdfName("FRM"));

        PdfCanvas topCanvas = new PdfCanvas(napp, document);
        topCanvas.addXObject(topLayer, 0, 0);

        return napp;
    }
}
