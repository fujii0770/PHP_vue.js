package com.hanko.app.bean;

import java.util.ArrayList;

/**
 * The Class SignatureRequest.
 */
public class SignatureRequest {

	/**  0:電子署名なし　1：電子署名あり. */
	private boolean signature;

	/** signatureKeyFile */
	private String signatureKeyFile;

	/** signatureKeyPassword */
	private String signatureKeyPassword;

	/** The pdfs. */
	private ArrayList<PdfRequest> data;

	/** signature reason */
	private String signatureReason;

	/** timestamp signature reason */
	private String timestampSignatureReason;

	/** document timestamp signature reason */
	private String documentTimestampSignatureReason;

	private boolean usingNewSignatureForTas;

	/**
	 * Gets the signature.
	 *
	 * @return the signature
	 */
	public boolean getSignature() {
		return signature;
	}

	/**
	 * Sets the signature.
	 *
	 * @param signature the new signature
	 */
	public void setSignature(boolean signature) {
		this.signature = signature;
	}

	/**
	 * Gets the data.
	 *
	 * @return the pdfs
	 */
	public ArrayList<PdfRequest> getData() {
		return data;
	}

	/**
	 * Sets the data.
	 *
	 * @param pdfs the new pdfs
	 */
	public void setData(ArrayList<PdfRequest> pdfs) {
		this.data = pdfs;
	}

	/**
	 * Gets the signatureKeyFile.
	 *
	 * @return the signatureKeyFile
	 */
	public String getSignatureKeyFile() {
		return signatureKeyFile;
	}

	/**
	 * Sets the signatureKeyFile.
	 *
	 * @param signatureKeyFile the new signatureKeyFile
	 */
	public void setSignatureKeyFile(String signatureKeyFile) {
		this.signatureKeyFile = signatureKeyFile;
	}

	/**
	 * Gets the signatureKeyPassword.
	 *
	 * @return the signatureKeyPassword
	 */
	public String getSignatureKeyPassword() {
		return signatureKeyPassword;
	}

	/**
	 * Sets the signatureKeyPassword.
	 *
	 * @param signatureKeyPassword the new signatureKeyPassword
	 */
	public void setSignatureKeyPassword(String signatureKeyPassword) {
		this.signatureKeyPassword = signatureKeyPassword;
	}

	/**
	 * Gets the signatureReason.
	 *
	 * @return the signatureReason
	 */
	public String getSignatureReason() {
		return signatureReason;
	}

	/**
	 * Sets the signatureReason.
	 *
	 * @param signatureReason the new signatureReason
	 */
	public void setSignatureReason(String signatureReason) {
		this.signatureReason = signatureReason;
	}

	/**
	 * Gets the timestampSignatureReason.
	 *
	 * @return the timestampSignatureReason
	 */
	public String getTimestampSignatureReason() {
		return timestampSignatureReason;
	}

	/**
	 * Sets the timestampSignatureReason.
	 *
	 * @param timestampSignatureReason the new timestampSignatureReason
	 */
	public void setTimestampSignatureReason(String timestampSignatureReason) {
		this.timestampSignatureReason = timestampSignatureReason;
	}

	/**
	 * Gets the usingNewSignatureForTas.
	 *
	 * @return the usingNewSignatureForTas
	 */
	public boolean isUsingNewSignatureForTas() {
		return usingNewSignatureForTas;
	}

	/**
	 * Sets the usingNewSignatureForTas.
	 *
	 * @param usingNewSignatureForTas the new usingNewSignatureForTas
	 */
	public void setUsingNewSignatureForTas(boolean usingNewSignatureForTas) {
		this.usingNewSignatureForTas = usingNewSignatureForTas;
	}

	/**
	 * Gets the documentTimestampSignatureReason.
	 *
	 * @return the documentTimestampSignatureReason
	 */
	public String getDocumentTimestampSignatureReason() {
		return documentTimestampSignatureReason;
	}

	/**
	 * Sets the documentTimestampSignatureReason.
	 *
	 * @param documentTimestampSignatureReason the new documentTimestampSignatureReason
	 */
	public void setDocumentTimestampSignatureReason(String documentTimestampSignatureReason) {
		this.documentTimestampSignatureReason = documentTimestampSignatureReason;
	}
}
