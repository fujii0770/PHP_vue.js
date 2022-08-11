package com.hanko.app.bean;

public class PdfResponse {

	/** The circular document id. */
	private Integer circular_document_id;

	/**
	 * base64形式のPDFデータ
	 */
	private String pdf_data;

	/**
	 * Getter pdf_data
	 * @return
	 */
	public String getPdf_data() {
		return pdf_data;
	}
	/**
	 * Setter pdf_data
	 * @param pdf_data
	 */
	public void setPdf_data(String pdf_data) {
		this.pdf_data = pdf_data;
	}

	/**
	 * Sets the circular document id.
	 *
	 * @param circular document id the id of file
	 */
	public void setCircular_document_id(Integer circular_document_id) {
		this.circular_document_id = circular_document_id;
	}

	/**
	 * Gets the circular document id.
	 *
	 * @return the circular document id
	 */
	public Integer getCircular_document_id() {
		return circular_document_id;
	}
}
