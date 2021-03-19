context('Student index page', () => {
  afterEach(() => {
    cy.logout()
  })

  it('Cannot view students without permission', () => {
    cy.login()

    cy.request({
      failOnStatusCode: false,
      url: '/students',
    }).then(res => {
      expect(res.status).to.eq(403);
    })

    cy.addPermissions(['view students'], true)
    cy.visit('/students')
    cy.getCy('page-title').should('contain.text', 'Students')
  })
})
