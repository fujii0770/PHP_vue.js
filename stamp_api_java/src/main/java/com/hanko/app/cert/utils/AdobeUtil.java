package com.hanko.app.cert.utils;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.GeneralSecurityException;
import java.security.cert.CertificateException;
import java.security.cert.X509Certificate;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

import org.bouncycastle.asn1.ASN1InputStream;
import org.bouncycastle.asn1.ASN1ObjectIdentifier;
import org.bouncycastle.asn1.ASN1OctetString;
import org.bouncycastle.asn1.ASN1Primitive;
import org.bouncycastle.asn1.ASN1Sequence;
import org.bouncycastle.asn1.ASN1TaggedObject;
import org.bouncycastle.asn1.x509.Extension;
import org.bouncycastle.cert.ocsp.OCSPException;
import org.bouncycastle.jce.provider.BouncyCastleProvider;
import org.bouncycastle.jce.provider.X509CertParser;
import org.bouncycastle.operator.OperatorCreationException;
import org.bouncycastle.x509.util.StreamParsingException;

import com.itextpdf.io.util.StreamUtil;
import com.itextpdf.kernel.PdfException;
import com.itextpdf.kernel.pdf.CompressionConstants;
import com.itextpdf.kernel.pdf.PdfArray;
import com.itextpdf.kernel.pdf.PdfCatalog;
import com.itextpdf.kernel.pdf.PdfDate;
import com.itextpdf.kernel.pdf.PdfDeveloperExtension;
import com.itextpdf.kernel.pdf.PdfDictionary;
import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfName;
import com.itextpdf.kernel.pdf.PdfStream;
import com.itextpdf.kernel.pdf.PdfVersion;
import com.itextpdf.signatures.ICrlClient;
import com.itextpdf.signatures.IOcspClient;
import com.itextpdf.signatures.OcspClientBouncyCastle;
import com.itextpdf.signatures.PdfPKCS7;
import com.itextpdf.signatures.PdfSignature;
import com.itextpdf.signatures.SignatureUtil;

/**
 * <a href=
 * "https://stackoverflow.com/questions/51370965/how-can-i-add-pades-ltv-using-itext">
 * how can I add PAdES-LTV using itext </a> <br/>
 * <a href=
 * "https://stackoverflow.com/questions/51639464/itext7-ltvverification-addverification-not-enabling-ltv">
 * iText7 LtvVerification.addVerification not enabling LTV </a>
 * <p>
 * This class adds LTV information to a signed PDF to make it LTV enabled as
 * reported by Adobe Acrobat.
 * </p>
 * <p>
 * It has originally been written for iText 5 in the context of the former
 * question. In the context of the latter one it has been ported to iText 7. As
 * a side effect some iText-5-isms may be contained in this code.
 * </p>
 *
 * @author mkl
 */
public class AdobeUtil {
	//
	// inner class
	//
	class ValidationData {
		final List<byte[]> crls = new ArrayList<byte[]>();
		final List<byte[]> ocsps = new ArrayList<byte[]>();
		final List<byte[]> certs = new ArrayList<byte[]>();
	}
	//
	// member variables
	//
	final PdfDocument pdfDocument;

	final Map<PdfName, ValidationData> validated = new HashMap<PdfName, ValidationData>();

	private Map<String, X509Certificate> mapCertificates;
	private X509Certificate extCertificate;

	private boolean isOnlineCertificate;

	/**
	 * Use this constructor with a {@link PdfDocument} in append mode. Otherwise the
	 * existing signatures will be damaged.
	 */
	public AdobeUtil(PdfDocument pdfDocument, Map<String, X509Certificate> mapCertificate,
			boolean isOnlineCertificate, X509Certificate extCertificate) {
		this.pdfDocument = pdfDocument;
		this.mapCertificates = mapCertificate;
		this.isOnlineCertificate = isOnlineCertificate;
		this.extCertificate = extCertificate;
	}

	/**
	 * Call this method to have LTV information added to the {@link PdfDocument}
	 * given in the constructor.
	 *
	 * @throws OCSPException
	 * @throws OperatorCreationException
	 */
	public void enableLtv(ICrlClient crl) throws GeneralSecurityException, IOException,
			StreamParsingException, OperatorCreationException, OCSPException {
		IOcspClient ocsp = new OcspClientBouncyCastle(null);

		enableLtv(ocsp, crl);
	}

	/**
	 * Call this method to have LTV information added to the {@link PdfDocument}
	 * given in the constructor.
	 *
	 * @throws OCSPException
	 * @throws OperatorCreationException
	 */
	public void enableLtv(IOcspClient ocspClient, ICrlClient crlClient) throws GeneralSecurityException, IOException,
			StreamParsingException, OperatorCreationException, OCSPException {
		SignatureUtil signatureUtil = new SignatureUtil(pdfDocument);

		List<String> names = signatureUtil.getSignatureNames();
		PdfSignature last = null;
		for (String name : names) {
			PdfPKCS7 pdfPKCS7 = signatureUtil.verifySignature(name, BouncyCastleProvider.PROVIDER_NAME);
			PdfSignature sig = signatureUtil.getSignature(name);
			last = sig;
			List<X509Certificate> certificatesToCheck = new ArrayList<>();
			certificatesToCheck.add(pdfPKCS7.getSigningCertificate());
			while (!certificatesToCheck.isEmpty()) {
				X509Certificate certificate = certificatesToCheck.remove(0);
				addLtvForChain(certificate, ocspClient, crlClient, LtvUtil.getSignatureHashKey(sig));
			}
		}
		if (this.extCertificate != null) {
			addLtvForChain(this.extCertificate, ocspClient, crlClient, LtvUtil.getSignatureHashKey(last));
		}

		outputDss();
	}

	//
	// the actual LTV enabling methods
	//
	private void addLtvForChain(X509Certificate certificate, IOcspClient ocspClient, ICrlClient crlClient, PdfName key)
			throws GeneralSecurityException, IOException, StreamParsingException, OperatorCreationException,
			OCSPException {
		ValidationData validationData = new ValidationData();

		while (certificate != null) {
			Logger.getLogger("Certificate Subject: " + AdobeUtil.class.getName()).log(Level.INFO,
					certificate.getSubjectX500Principal().getName());
			X509Certificate issuer = getIssuerCertificate(certificate);
			validationData.certs.add(certificate.getEncoded());
			byte[] ocspResponse = ocspClient.getEncoded(certificate, issuer, null);
			if (ocspResponse != null) {
				Logger.getLogger(AdobeUtil.class.getName()).log(Level.INFO, "with OCSP response");
				validationData.ocsps.add(ocspResponse);
				X509Certificate ocspSigner = LtvUtil.getOcspSignerCertificate(ocspResponse);
				if (ocspSigner != null) {
					Logger.getLogger(AdobeUtil.class.getName()).log(Level.INFO, "  signed by %s\n",
							ocspSigner.getSubjectX500Principal().getName());
				}
				addLtvForChain(ocspSigner, ocspClient, crlClient, LtvUtil.getOcspHashKey(ocspResponse));
			} else {
				Collection<byte[]> crl = crlClient.getEncoded(certificate, null);
				if (crl != null && !crl.isEmpty()) {
					Logger.getLogger(AdobeUtil.class.getName()).log(Level.INFO, "  with %s CRLs\n", crl.size());
					validationData.crls.addAll(crl);
					for (byte[] crlBytes : crl) {
						addLtvForChain(null, ocspClient, crlClient, LtvUtil.getCrlHashKey(crlBytes));
					}
				}
			}
			certificate = issuer;
		}

		validated.put(key, validationData);
	}

	private void outputDss() throws IOException {
		PdfDictionary dss = new PdfDictionary();
		PdfDictionary vrim = new PdfDictionary();
		PdfArray ocsps = new PdfArray();
		PdfArray crls = new PdfArray();
		PdfArray certs = new PdfArray();

		PdfCatalog catalog = pdfDocument.getCatalog();
		if (pdfDocument.getPdfVersion().compareTo(PdfVersion.PDF_2_0) < 0) {
			catalog.addDeveloperExtension(PdfDeveloperExtension.ESIC_1_7_EXTENSIONLEVEL5);
			catalog.addDeveloperExtension(new PdfDeveloperExtension(PdfName.ADBE, new PdfName("1.7"), 8));
		}

		for (PdfName vkey : validated.keySet()) {
			PdfArray ocsp = new PdfArray();
			PdfArray crl = new PdfArray();
			PdfArray cert = new PdfArray();
			PdfDictionary vri = new PdfDictionary();
			for (byte[] b : validated.get(vkey).crls) {
				PdfStream ps = new PdfStream(b);
				ps.setCompressionLevel(CompressionConstants.DEFAULT_COMPRESSION);
				ps.makeIndirect(pdfDocument);
				crl.add(ps);
				crls.add(ps);
				crls.setModified();
			}
			for (byte[] b : validated.get(vkey).ocsps) {
				b = LtvUtil.buildOCSPResponse(b);
				PdfStream ps = new PdfStream(b);
				ps.setCompressionLevel(CompressionConstants.DEFAULT_COMPRESSION);
				ps.makeIndirect(pdfDocument);
				ocsp.add(ps);
				ocsps.add(ps);
				ocsps.setModified();
			}
			for (byte[] b : validated.get(vkey).certs) {
				PdfStream ps = new PdfStream(b);
				ps.setCompressionLevel(CompressionConstants.DEFAULT_COMPRESSION);
				ps.makeIndirect(pdfDocument);
				cert.add(ps);
				certs.add(ps);
				certs.setModified();
			}
			if (ocsp.size() > 0) {
				ocsp.makeIndirect(pdfDocument);
				vri.put(PdfName.OCSP, ocsp);
			}
			if (crl.size() > 0) {
				crl.makeIndirect(pdfDocument);
				vri.put(PdfName.CRL, crl);
			}
			if (cert.size() > 0) {
				cert.makeIndirect(pdfDocument);
				vri.put(PdfName.Cert, cert);
			}
			vri.put(PdfName.TU, new PdfDate().getPdfObject());
			vri.makeIndirect(pdfDocument);
			vrim.put(vkey, vri);
		}
		vrim.makeIndirect(pdfDocument);
		vrim.setModified();
		dss.put(PdfName.VRI, vrim);
		if (ocsps.size() > 0) {
			ocsps.makeIndirect(pdfDocument);
			dss.put(PdfName.OCSPs, ocsps);
		}
		if (crls.size() > 0) {
			crls.makeIndirect(pdfDocument);
			dss.put(PdfName.CRLs, crls);
		}
		if (certs.size() > 0) {
			certs.makeIndirect(pdfDocument);
			dss.put(PdfName.Certs, certs);
		}

		dss.makeIndirect(pdfDocument);
		dss.setModified();
		catalog.put(PdfName.DSS, dss);
	}

	//
	// X509 certificate related helpers
	//
	private X509Certificate getIssuerCertificate(X509Certificate certificate)
			throws IOException, StreamParsingException, CertificateException {
		if (isOnlineCertificate) {
			String url = getCACURL(certificate);
			if (url != null && url.length() > 0) {
				HttpURLConnection con = (HttpURLConnection) new URL(url).openConnection();
				if (con.getResponseCode() / 100 != 2) {
					throw new PdfException(PdfException.InvalidHttpResponse1).setMessageParams(con.getResponseCode());
				}
				InputStream inp = (InputStream) con.getContent();
				X509CertParser parser = new X509CertParser();
				parser.engineInit(new ByteArrayInputStream(StreamUtil.inputStreamToArray(inp)));
				return (X509Certificate) parser.engineRead();
			}
			return null;
		} else {
			X509Certificate parentCertificate = null;
			if (mapCertificates != null && mapCertificates.size() > 0) {
				for (String cnCert : mapCertificates.keySet()) {
					if (certificate.getSubjectX500Principal().getName().indexOf(cnCert) > -1) {
						parentCertificate = mapCertificates.get(cnCert);
						break;
					}
				}

			}
			return parentCertificate;
		}

		/*
		 * if
		 * (certificate.getSubjectX500Principal().getName().indexOf("CN=Shachihata Inc")
		 * > -1) { FileInputStream fr2 = new FileInputStream(
		 * "T:\\pac\\stamp_api_java\\src\\main\\resources\\public_id_intermediate.cer");
		 * CertificateFactory cf = CertificateFactory.getInstance("X509"); return
		 * (X509Certificate)cf.generateCertificate(fr2); }else if
		 * (certificate.getSubjectX500Principal().getName().
		 * indexOf("CN=SECOM Passport for Member PUB CA8") > -1) { FileInputStream fr =
		 * new FileInputStream(
		 * "T:\\pac\\stamp_api_java\\src\\main\\resources\\public_id_root.cer");
		 * CertificateFactory cf = CertificateFactory.getInstance("X509"); return
		 * (X509Certificate)cf.generateCertificate(fr); }else { return null; }
		 */

	}

	static String getCACURL(X509Certificate certificate) {
		ASN1Primitive obj;
		try {
			obj = getExtensionValue(certificate, Extension.authorityInfoAccess.getId());
			if (obj == null) {
				return null;
			}
			ASN1Sequence AccessDescriptions = (ASN1Sequence) obj;
			for (int i = 0; i < AccessDescriptions.size(); i++) {
				ASN1Sequence AccessDescription = (ASN1Sequence) AccessDescriptions.getObjectAt(i);
				if (AccessDescription.size() != 2) {
					continue;
				} else if (AccessDescription.getObjectAt(0) instanceof ASN1ObjectIdentifier) {
					ASN1ObjectIdentifier id = (ASN1ObjectIdentifier) AccessDescription.getObjectAt(0);
					if ("1.3.6.1.5.5.7.48.2".equals(id.getId())) {
						ASN1Primitive description = (ASN1Primitive) AccessDescription.getObjectAt(1);
						String AccessLocation = getStringFromGeneralName(description);
						if (AccessLocation == null) {
							return "";
						} else {
							return AccessLocation;
						}
					}
				}
			}
		} catch (IOException e) {
			return null;
		}
		return null;
	}

	static ASN1Primitive getExtensionValue(X509Certificate certificate, String oid) throws IOException {
		byte[] bytes = certificate.getExtensionValue(oid);
		if (bytes == null) {
			return null;
		}
		ASN1InputStream aIn = new ASN1InputStream(new ByteArrayInputStream(bytes));
		ASN1OctetString octs = (ASN1OctetString) aIn.readObject();
		aIn = new ASN1InputStream(new ByteArrayInputStream(octs.getOctets()));
		return aIn.readObject();
	}

	private static String getStringFromGeneralName(ASN1Primitive names) throws IOException {
		ASN1TaggedObject taggedObject = (ASN1TaggedObject) names;
		return new String(ASN1OctetString.getInstance(taggedObject, false).getOctets(), "ISO-8859-1");
	}
}