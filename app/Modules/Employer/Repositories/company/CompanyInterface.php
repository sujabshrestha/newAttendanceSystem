<?php

namespace Employer\Repositories\company;


interface CompanyInterface
{


    public function getAllCompanies();

    public function store($request);

    public function update($request, $id);


    public function getCompanyById($id);

    public function getCompanyByIdWithCandidates($id);

    public function getCompaniesByEmployerId();


    public function activeCompaniesByEmployerID($id);

    public function inactiveCompaniesByEmployerID($id);


}
